<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class PackageController extends Controller
{
    public function bookPackage(Request $request)
    {
        // validations
        $tourValidator = $this->validateTourParams($request);

        if ($tourValidator->fails()) {
            return ApiResponse::error($tourValidator->errors());
        }

        $flightValidator = $this->validateFlightParams($request);

        if ($flightValidator->fails()) {
            return ApiResponse::error($flightValidator->errors());
        }

        // book tour
        $tourBody = $request->input('tour');
        $tourResponse = TourRadarController::createNewBooking($tourBody);
        if (isset($tourResponse['status']) && $tourResponse['status'] !== "confirmed") {
            return $tourResponse;
        }

        // book flights
        $flightBody = $request->input('flight');
        $flightResponse = DuffelApiController::createNewBooking($flightBody);
        if (isset($flightResponse['errors'])) {
            return response()->json([
                "tourResponse" => $tourResponse,
                "flightResponse" => $flightResponse,
            ]);
        }
        return response()->json([
            "tourResponse" => $tourResponse,
            "flightResponse" => $flightResponse,
        ]);
    }

    private function validateTourParams($request)
    {
        $rules = [
            'tour.departure_id' => 'required',
            'tour.user_country' => 'required',
            'tour.currency' => 'required|in:AUD,CAD,EUR,GBP,NZD,USD',
            'tour.email' => 'required',
            'tour.passengers' => 'required|array',
            'tour.passengers.*.pax_number' => 'required',
            'tour.passengers.*.price_category_id' => 'required',
            'tour.passengers.*.fields' => 'required|array',
        ];

        return Validator::make($request->all(), $rules);
    }

    private function validateFlightParams($request)
    {
        $rules = [
            'flight.data.selected_offers' => 'required', // OFFER_ID

            'flight.data.payments' => 'required|array',
            'flight.data.payments.*.type' => 'required|in:arc_bsp_cash,balance',
            'flight.data.payments.*.currency' => 'required', // TOTAL_CURRENCY
            'flight.data.payments.*.amount' => 'required', // TOTAL_AMOUNT

            'flight.data.passengers' => 'required|array',
            'flight.data.passengers.*.id' => 'required', // ADULT_PASSENGER_ID_1
            'flight.data.passengers.*.given_name' => 'required',
            'flight.data.passengers.*.family_name' => 'required',
            'flight.data.passengers.*.gender' => 'required',
            'flight.data.passengers.*.title' => 'required|in:mr,ms,mrs,miss,dr',
            'flight.data.passengers.*.born_on' => 'required|date_format:Y-m-d',
            'flight.data.passengers.*.email' => 'required',
            'flight.data.passengers.*.phone_number' => 'required',
        ];
        $messages = [
            'flight.data.passengers.*.title.in' => "The title must be one of the following: 'mr', 'ms', 'mrs', 'miss', 'dr'",
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}

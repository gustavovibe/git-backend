<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Helpers\FormatTour;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\TourRadarController;
use App\Helpers\FormatDepartures;

class ProxyTourRadarController extends Controller
{
    public function show($id)
    {
        $tour = TourRadarController::getTour($id);
        $tour = FormatTour::formatTourData($tour);
        return ApiResponse::success($tour);
    }

    public function departures(Request $request)
    {
        $rules = [
            'tourId' => 'required',
            'currency' => 'sometimes|in:AUD,CAD,EUR,GBP,NZD,USD',
            'page' => 'sometimes',
            'user_country' => 'nullable|integer|min:0',
            'date_range' => 'sometimes|regex:/^\d{8}-\d{8}$/',
        ];
        $messages = [
            'tourId.required' => 'El campo :attribute es obligatorio.',
            'currency.in' => 'El campo :attribute debe ser uno de los siguientes valores: AUD, CAD, EUR, GBP, NZD, USD.',
            'date_range.regex' => 'El campo :attribute debe tener el formato YYYYMMDD-YYYYMMDD.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getDeparturesByTour($request->all());
        if (isset($response['items'])) {
            $response['items'] = FormatDepartures::formatDeparturesResponse($response['items'] ?? []);
        }
        return ApiResponse::success($response);
    }

    public function prices(Request $request)
    {
        $rules = [
            'tourId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getPriceCategoriesByTour($request['tourId']);
        return ApiResponse::success($response);
    }

    public function bookingFields(Request $request)
    {
        $rules = [
            'operatorId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }
        $response = TourRadarController::getOperatorBookingFields();
        return ApiResponse::success($response);
    }

    public function bookingsList()
    {
        $response = TourRadarController::getBookingsList();
        return ApiResponse::success($response);
    }

    public function bookingsStore(Request $request)
    {
        $rules = [
            'departure_id' => 'required',
            'user_country' => 'required',
            'currency' => 'required|in:AUD,CAD,EUR,GBP,NZD,USD',
            'email' => 'required',
            'passengers' => 'required|array',
            'passengers.*.pax_number' => 'required',
            'passengers.*.price_category_id' => 'required',
            'passengers.*.fields' => 'required|array',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::createNewBooking($request->all());
        return $response;
    }
}

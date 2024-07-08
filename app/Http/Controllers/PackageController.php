<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\Traveler;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class PackageController extends Controller
{
    public function bookPackage(TourStoreRequest $request)
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
        // $tourResponse = TourRadarController::createNewBooking($tourBody);
        // if (isset($tourResponse['status']) && $tourResponse['status'] !== "confirmed") {
        //     return response()->json([
        //         "tourResponse" => $tourResponse,
        //         "flightResponse" => null,
        //     ]);
        // }

        // book flights
        $flightBody = $request->input('flight');
        $flightResponse = DuffelApiController::createNewBooking($flightBody);
        if (isset($flightResponse['errors'])) {
            return response()->json([
                // "tourResponse" => $tourResponse,
                "flightResponse" => $flightResponse,
            ]);
        }


        $passengers =    $tourBody['passengers'];
        $firstIteration = true;
        foreach ($passengers as $passenger) {
            if ($firstIteration) {
                $user = User::updateOrCreate(
                    ['email' => $passenger['fields']['email']],
                    [
                        'name' => $passenger['fields']['first_name'] . " " . $passenger['fields']['last_name'],
                        'password' => Hash::make('password123'),
                        'profile_id' => 2,
                        'phone' => $passenger['fields']['phone_number'],
                        'country' => $passenger['fields']['country'],
                        'role' => 'role',
                        'active' => 1,
                        'suscribed' => 1,
                        'hear' => "without comment",
                    ]
                );
                $firstIteration = false;
            }
            $traveler = Traveler::create([
                'title' => $passenger['fields']['title'],
                'gender' => $passenger['fields']['gender'],
                'name' => $passenger['fields']['first_name'],
                'last' => $passenger['fields']['last_name'],
                'birth' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth'])->format('Y-m-d'),
                'passport' => intval($passenger['fields']['passport_number']),
                'place' => $passenger['fields']['place_of_issue'],
                'issue' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['issue_date'])->format('Y-m-d'),
                'expire' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['expiration_date'])->format('Y-m-d'),
                'mail' => $passenger['fields']['email'],
                'phone' => $passenger['fields']['phone_number'],
                'address' => $passenger['fields']['address'],
                'country' => $passenger['fields']['country'],
                'lead' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        return response()->json([
            // "tourResponse" => $tourResponse,
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

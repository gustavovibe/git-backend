<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class DuffelApiController extends Controller
{
    public function offerRequests(Request $request)
    {
        $rules = [
            'origin' => 'required',
            'destination' => 'required',
            'departureDate' => 'required',
            'originInbound' => 'sometimes',
            'destinationInbound' => 'sometimes',
            'departureDateInbound' => 'sometimes',
            'adultsCount' => 'sometimes|integer|min:1',
            'childrenCount' => 'sometimes|integer|min:0',
            'cabinClass' => 'sometimes|in:first,business,premium_economy,economy',
        ];
        $messages = [
            'cabinClass.in' => "El campo :attribute debe ser uno de los siguientes valores: 'first' 'business' 'premium_economy' 'economy'",
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $slices = [
            [
                'origin' => $request->origin,
                'destination' => $request->destination,
                'departure_date' => $request->departureDate,
            ]
        ];

        if ($request->has('originInbound') && $request->has('destinationInbound') && $request->has('departureDateInbound')) {
            $inboundSlice = [
                'origin' => $request->originInbound,
                'destination' => $request->destinationInbound,
                'departure_date' => $request->departureDateInbound,
            ];
            array_push($slices, $inboundSlice);
        }

        $passengers = $this->getPassengers($request);

        try {
            // Construct the request body
            $requestBody = [
                'data' => [
                    'slices' => $slices,
                    'passengers' => $passengers,
                    'cabin_class' => $request->cabinClass ?? null
                ]
            ];

            $headers = [
                'Accept-Encoding' => 'gzip',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Duffel-Version' => 'v1',
                'Authorization' => 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho',
            ];
            $url = 'https://api.duffel.com/air/offer_requests';
            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->post($url, $requestBody);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getPassengers($request)
    {
        $one_adult = ['type' => 'adult'];
        if (!$request->has('adultsCount')) {
            return [$one_adult];
        }
        $passengers = [];
        for ($i = 0; $i < $request->adultsCount; $i++) {
            array_push($passengers, $one_adult);
        }
        if ($request->has('childrenCount')) {
            $one_child = ['age' => 15];
            for ($i = 0; $i < $request->childrenCount; $i++) {
                array_push($passengers, $one_child);
            }
        }
        return $passengers;
    }
}

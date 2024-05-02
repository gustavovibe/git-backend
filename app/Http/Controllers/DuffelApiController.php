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
            'supplierTimeout' => 'sometimes',
            'limit' => 'sometimes',
            'sort' => 'sometimes',
            'maxConnections' => 'sometimes',
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
            $url = 'https://api.duffel.com/air/offer_requests?';
            $url = $this->addMoreQueryparamsToUrl($url, $request);

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->post($url, $requestBody);

            // Return the response from the Duffel API
            $response = $response->json();
            if (isset($response['data']['offers'])) {
                $offersQuantity = $request->has('limit') ? $request->limit : 5;
                $response['data']['offers'] = $this->getFilteredOffers($response['data']['offers'], $offersQuantity);
            }
            return $response;
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function singleRequest(Request $request)
    {
        $rules = [
            'requestId' => 'required|string|regex:/^orq_.+$/',
        ];
        $messages = [
            'requestId.regex' => 'El campo :attribute debe comenzar diciendo "orq_".',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            $headers = [
                'Accept-Encoding' => 'gzip',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Duffel-Version' => 'v1',
                'Authorization' => 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho',
            ];
            $url = 'https://api.duffel.com/air/offer_requests/' . $request->requestId;
            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function singleOffer(Request $request)
    {
        $rules = [
            'offerId' => 'required|string|regex:/^off_.+$/',
        ];
        $messages = [
            'offerId.regex' => 'El campo :attribute debe comenzar diciendo "off_".',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            $headers = [
                'Accept-Encoding' => 'gzip',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Duffel-Version' => 'v1',
                'Authorization' => 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho',
            ];
            $url = 'https://api.duffel.com/air/offers/' . $request->offerId;
            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getFilteredOffers($offers, $offersQuantity)
    {
        $filteredOffers = [];
        $count = 0; // Variable to keep track of filtered offers count

        // Iterate over the offers
        foreach ($offers as $offer) {
            if ($count >= $offersQuantity) {
                break; // Exit the loop if we've already reached the required quantity
            }

            $skipOffer = false; // Variable to determine whether to skip this offer

            foreach ($offer['slices'] as $slice) {
                if (!isset($slice['segments'])) {
                    continue; // Skip this section if 'segments' is absent
                }

                foreach ($slice['segments'] as $segment) {
                    if (!isset($segment['operating_carrier']['name'])) {
                        continue; // Skip this segment if 'operating_carrier' or 'name' is absent
                    }

                    if ($segment['operating_carrier']['name'] === 'Duffel Airways') {
                        $skipOffer = true; // Set the flag to skip this offer
                        break 2; // Exit the nested loops
                    }
                }
            }

            if (!$skipOffer) {
                $filteredOffers[] = $offer; // Add the offer if it shouldn't be skipped
                $count++; // Increment the count of filtered offers
            }
        }

        return $filteredOffers;
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

    private function addMoreQueryparamsToUrl($url, $request)
    {
        $default_supplierTimeout = 5000;
        $default_limit = 5;
        $default_sort = "total_amount";
        $default_maxConnections = 1;

        if ($request->has('supplierTimeout')) {
            $url .= "supplier_timeout=" . $request->supplierTimeout . "&";
        } else {
            $url .= "supplier_timeout=" . $default_supplierTimeout . "&";
        }

        if ($request->has('limit')) {
            $url .= "limit=" . $request->limit . "&";
        } else {
            $url .= "limit=" . $default_limit . "&";
        }

        if ($request->has('sort')) {
            $url .= "sort=" . $request->sort . "&";
        } else {
            $url .= "sort=" . $default_sort . "&";
        }

        if ($request->has('maxConnections')) {
            $url .= "max_connections=" . $request->maxConnections . "&";
        } else {
            $url .= "max_connections=" . $default_maxConnections . "&";
        }

        return $url;
    }
}

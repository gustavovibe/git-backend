<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GustavoDuffelController extends Controller
{
    public function offerRequests(Request $request)
    {
        // Retrieve query parameters from the request
        $origin = $request->query('origin');
        $startCity = $request->query('startCity');
        $endCity = $request->query('endCity');
        $departureDate = $request->query('departure');
        $arrivalDate = $request->query('arrival');
        $adultsCount = $request->query('adultsCount');
        $childrenCount = $request->query('childrenCount');

        // Validate that required parameters are provided
        $missingParameters = [];
        if (!$origin) {
            $missingParameters[] = 'origin';
        }
        if (!$startCity) {
            $missingParameters[] = 'startCity';
        }
        if (!$endCity) {
            $missingParameters[] = 'endCity';
        }
        if (!$departureDate) {
            $missingParameters[] = 'departure';
        }
        if (!$arrivalDate) {
            $missingParameters[] = 'arrival';
        }
        if (!$adultsCount) {
            $missingParameters[] = 'adultsCount';
        }
        if (!$childrenCount) {
            $missingParameters[] = 'childrenCount';
        }

        if (!empty($missingParameters)) {
            return response()->json(['error' => 'Missing required parameters: ' . implode(', ', $missingParameters)], 400);
        }


        try {
            // Construct the passengers array based on counts
            $passengers = [];
            for ($i = 0; $i < $adultsCount; $i++) {
                $passengers[] = ['type' => 'adult'];
            }
            for ($i = 0; $i < $childrenCount; $i++) {
                $passengers[] = ['type' => 'child'];
            }

            // Construct the request body
            $requestBody = [
                'data' => [
                    'slices' => [
                        [
                            'origin' => $origin,
                            'destination' => $startCity,
                            'departure_date' => $departureDate,
                        ],
                        [
                            'origin' => $endCity,
                            'destination' => $origin,
                            'departure_date' => $arrivalDate,
                        ]
                    ],
                    'passengers' => $passengers,
                    'cabin_class' => null
                ]
            ];
            // Make the request to the Duffel API
            $response = Http::withHeaders([
                'Accept-Encoding' => 'gzip',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Duffel-Version' => 'v1',
                'Authorization' => 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho',
            ])->post('https://api.duffel.com/air/offer_requests?supplier_timeout=5000&limit=5&sort=total_amount&max_connections=1', $requestBody);

            if ($response->status() >= 400) {
                return $response->json();
            }

            // Extract the offers from the response
            $offers = $response->json()['data']['offers'];

            // Filter out offers with operating carrier name "Duffel Airways"
            // Filter out offers with operating carrier name "Duffel Airways"
            $filteredOffers = array_filter($offers, function ($offer) {
                foreach ($offer['slices'] as $slice) {
                    if (!isset($slice['segments'])) {
                        continue; // Skip this slice if 'segments' key is missing
                    }
                    foreach ($slice['segments'] as $segment) {
                        if (!isset($segment['operating_carrier']['name'])) {
                            continue; // Skip this segment if 'operating_carrier' or 'name' key is missing
                        }
                        if ($segment['operating_carrier']['name'] === 'Duffel Airways') {
                            return false; // Skip this offer if operating carrier is "Duffel Airways"
                        }
                    }
                }
                return true; // Include this offer if operating carrier is not "Duffel Airways"
            });


            // Filter offers with at least one checked or carry-on baggage
            $baggageOffers = array_filter($filteredOffers, function ($offer) {
                foreach ($offer['slices'] as $slice) {
                    if (!isset($slice['segments'])) {
                        continue; // Skip this slice if 'segments' key is missing
                    }
                    foreach ($slice['segments'] as $segment) {
                        if (!isset($segment['passengers'])) {
                            continue; // Skip this segment if 'passengers' key is missing
                        }
                        foreach ($segment['passengers'] as $passenger) {
                            if (!isset($passenger['baggages'])) {
                                continue; // Skip this passenger if 'baggages' key is missing
                            }
                            foreach ($passenger['baggages'] as $baggage) {
                                if ($baggage['type'] === 'checked' || $baggage['type'] === 'carry_on') {
                                    return true;
                                }
                            }
                        }
                    }
                }
                return false;
            });



            // Take only the first 5 offers
            $firstFiveOffers = array_slice($filteredOffers, 0, 5);

            // Return the filtered and limited offers
            return response()->json($firstFiveOffers);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

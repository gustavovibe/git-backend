<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $page = $request->query('page', 1); // Default to page 1 if not provided

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
               'Accept-Encoding' => 'gzip, deflate, br',
                'Accept' => 'application/json',
                'Duffel-Version' => 'v2',
                'Authorization' => 'Bearer duffel_test_tfNofacp8LVcPjSf7OA0Q78ghrmuoakwtBhjbxaRrs2',
            ])->post('https://api.duffel.com/air/offer_requests?supplier_timeout=5000&limit=5&sort=total_amount&max_connections=1', $requestBody);

            if ($response->status() >= 400) {
                return $response->json();
            }

            // Extract the offers from the response
            $offers = $response->json()['data']['offers'];

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

            $filteredOffers = array_values($baggageOffers); // Re-index the array to remove numeric keys

            // Extract the offers from the response
            $offers = collect($filteredOffers);
            // Paginate the offers with 3 offers per page
            $perPage = 3;

            $paginatedOffers = new LengthAwarePaginator(
                $offers->forPage($page, $perPage),
                $offers->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Return the paginated offers along with total number of pages
            return response()->json([
                'offers' => $paginatedOffers->items(),
                'total_pages' => $paginatedOffers->lastPage()
            ]);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

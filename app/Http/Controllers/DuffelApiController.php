<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DuffelApiController extends Controller
{
    public function offerRequests(Request $request)
    {
        // Retrieve query parameters from the request
        $origin = $request->query('origin');
        $destination = $request->query('destination');
        $departureDate = $request->query('departure');

        // Validate that required parameters are provided
        if (!$origin || !$destination || !$departureDate) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        try {
            // Construct the request body
            $requestBody = [
                'data' => [
                    'slices' => [
                        [
                            'origin' => $origin,
                            'destination' => $destination,
                            'departure_date' => $departureDate,
                        ]
                    ],
                    'passengers' => [['type' => 'adult']],
                    'cabin_class' => null
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
}

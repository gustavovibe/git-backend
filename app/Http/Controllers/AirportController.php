<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AirportController extends Controller
{
    public function getAirports()
    {
        $client = new Client();
        $url = 'https://api.duffel.com/air/airports';
        $authorization = 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho';
        $headers = [
            'Accept' => 'application/json',
            'Duffel-Version' => 'v1',
            'Authorization' => $authorization
        ];

        $airports = [];
        $after = null;
        $limit = 200;

        do {
            // Prepare the query parameters
            $query = [
                'limit' => $limit,
                'after' => $after
            ];

            // Make the request
            $response = $client->get($url, [
                'headers' => $headers,
                'query' => $query
            ]);

            // Decode the response
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Filter and collect the required fields
            foreach ($responseData['data'] as $airport) {
                $airports[] = [
                    'iata_city_code' => $airport['iata_city_code'],
                    'city_name' => $airport['city_name'],
                    'iata_country_code' => $airport['iata_country_code']
                ];
            }

            // Get the 'after' parameter for the next request
            $after = $responseData['meta']['after'] ?? null;

        } while ($after); // Continue while there is an 'after' parameter

        // Return the aggregated data
        return response()->json($airports);
    }
}

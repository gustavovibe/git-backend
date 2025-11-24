<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AirportController extends Controller
{

    /**
     * Get airports.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @return array
     * 
     */
    public function getAirports()
    {
        $client = new Client();
        $url = 'https://api.duffel.com/air/airports';
        $authorization = 'Bearer duffel_test_tfNofacp8LVcPjSf7OA0Q78ghrmuoakwtBhjbxaRrs2';

        $headers = [
            'Accept' => 'application/json',
            'Duffel-Version' => 'v1',
            'Authorization' => $authorization
        ];

        $airports = [];
        $processedIataCodes = []; // Array to keep track of already processed iata_city_codes
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

            // Filter and collect the required fields while checking for duplicates
            foreach ($responseData['data'] as $airport) {
                // Use 'iata_city_code' to check for duplicates
                if (!in_array($airport['iata_city_code'], $processedIataCodes)) {
                    $airports[] = [
                        'iata_city_code' => $airport['iata_city_code'],
                        'city_name' => $airport['city_name'],
                        'iata_country_code' => $airport['iata_country_code']
                    ];

                    // Add the iata_city_code to the processed list to avoid duplicates
                    $processedIataCodes[] = $airport['iata_city_code'];
                }
            }

            // Get the 'after' parameter for the next request
            $after = $responseData['meta']['after'] ?? null;

        } while ($after); // Continue while there is an 'after' parameter

        // Return the aggregated data
        return response()->json($airports);
    }
}

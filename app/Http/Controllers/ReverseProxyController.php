<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Helpers\ApiResponse;

class ReverseProxyController extends Controller
{

    /**
     * Proxy location.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function proxyLocation(Request $request)
    {

        $url = 'https://api.tequila.kiwi.com/locations/query';

        $params = [
            'term' => $request->q,
            'locale' => 'en-US',
            'location_types' => 'city',
            'limit' => 10,
            'active_only' => true,
        ];

        $client = new Client();

        try {
            $response = $client->get($url, [
                'query' => $params,
                'headers' => [
                    'accept' => 'application/json',
                    'apikey' => 'QGWd943iYcYa581oo0nm_m8Kl-BOL0an',
                ],
                'verify' => false,
            ]);

            $body = $response->getBody()->getContents();

            $data = json_decode($body, true);

            $locations = array_map(function ($location) {
                return [
                    'id' => $location['id'],
                    'name' => $location['name']
                ];
            }, $data['locations']);



            return ApiResponse::success($locations);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

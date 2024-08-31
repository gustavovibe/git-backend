<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;

class TourRadarController extends Controller
{

    public static function getAccessToken()
    {

        // ToDo: Move these variables to a .env file
        $clientId = env('TOURRADAR_CLIENT_ID', 'hpg0tvme3ujrwcnd6fcyttwst8');
        $clientSecret = env('TOURRADAR_CLIENT_SECRET', 'mjjqpzhg19rifw174ehlw1a56nufbvwxrcya2w4bz32dsbjf594');
        $urlToken = 'https://oauth.api.sandbox.b2b.tourradar.com/oauth2/token';
        $authorization = base64_encode($clientId . ':' . $clientSecret);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Basic " . $authorization,
        ];
        $body = [
            'grant_type' => 'client_credentials',
            'scope' => [
                'com.tourradar.tours/read',
                'com.tourradar.operators/read'
            ],
        ];

        try {
            $response = Http::withHeaders($headers)->asForm()->post($urlToken, $body);
            $data = $response->json();
            return $data['access_token'];
        } catch (RequestException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public static function getDeparturesByTour($params)
    {
        $accessToken = self::getAccessToken();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        $tourId = $params['tourId'];
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/departures?";

        if (isset($params['currency'])) {
            $url .= "currency=" . $params['currency'] . "&";
        }
        if (isset($params['page'])) {
            $url .= "page=" . $params['page'] . "&";
        }
        if (isset($params['user_country'])) {
            $url .= "user_country=" . $params['user_country'] . "&";
        }
        if (isset($params['date_range'])) {
            $url .= "date_range=" . $params['date_range'] . "&";
        }

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMultipleDeparturesByTours(Request $request)
    {
        $params = $request->all();

        if (!isset($params['tourIds'])) {
            return response()->json(['error' => 'tourIds parameter is required'], 400);
        }

        $tourIds = explode(',', $params['tourIds']);
        $departures = [];
        $itemsPerPage = 10;
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $start = ($page - 1) * $itemsPerPage;
        $end = $start + $itemsPerPage;
        
        Log::info('Starting to fetch departures', [
            'tourIds' => $tourIds,
            'params' => $params,
            'start' => $start,
            'end' => $end,
        ]);

        $tourIds = array_slice($tourIds, $start, $itemsPerPage);

        foreach ($tourIds as $tourId) {
            $params['tourId'] = $tourId;
            $params['page'] = 1; // Always fetch first page of departures for each tourId
            $response = $this->getDeparturesByTourParams($params);

            if (isset($response['items'])) {
                Log::info('Departures found for tour', ['tourId' => $tourId, 'departures' => $response['items']]);

                $cheapestDeparture = null;
                foreach ($response['items'] as $departure) {
                    if (isset($departure['prices']['price_total'])) {
                        $priceTotal = $departure['prices']['price_total'];
                        if ($cheapestDeparture === null || $priceTotal < $cheapestDeparture['prices']['price_total']) {
                            $departure['tourId'] = $tourId; // Add tourId to departure array
                            $cheapestDeparture = $departure;
                        }
                    }
                }
                if ($cheapestDeparture !== null) {
                    $departures[] = $cheapestDeparture;
                }
            } else {
                Log::info('No departures found for tour', ['tourId' => $tourId]);
            }

            sleep(0.1); // delay between API calls
        }

        Log::info('Returning departures', ['departures' => $departures]);

        return response()->json(['items' => $departures]);
    }

    private function getDeparturesByTourParams($params)
    {
        $accessToken = self::getAccessToken();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        $queryParams = [];

        if (isset($params['currency'])) {
            $queryParams['currency'] = $params['currency'];
        }
        if (isset($params['page'])) {
            $queryParams['page'] = $params['page'];
        }
        if (isset($params['user_country'])) {
            $queryParams['user_country'] = $params['user_country'];
        }
        if (isset($params['date_range'])) {
            // Modify the date_range parameter format to YYYYMMDD-YYYYMMDD
            $dateRange = explode('-', $params['date_range']);
            if (count($dateRange) === 2) {
                $formattedDateRange = implode('-', array_map(function ($date) {
                    return date('Ymd', strtotime($date));
                }, $dateRange));
                $queryParams['date_range'] = $formattedDateRange;

                // Validate the start date of the date range
                $startTimestamp = strtotime($dateRange[0]);
                $minimumTimestamp = strtotime('2024-05-25');

                if ($startTimestamp < $minimumTimestamp) {
                    return ['error' => 'Start date of the date range must be greater than or equal to 2024-05-25'];
                }
            } else {
                return ['error' => 'Invalid date_range format. It should be "YYYY/MM/DD,YYYY/MM/DD".'];
            }
        }
        if (isset($params['travelers'])) {
            $queryParams['travelers'] = $params['travelers'];
        }

        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$params['tourId']}/departures";


        try {
            $response = Http::withHeaders($headers)->get($url, $queryParams);
            $responseBody = $response->json();

            Log::info('API Response for Tour ' . $params['tourId'] . ':', $responseBody);

            if (!isset($responseBody['items'])) {
                return ['error' => 'Unexpected response structure', 'response' => $responseBody];
            }

            $departures = $responseBody['items'];

            $filteredDepartures = array_filter($departures, function ($departure) use ($params) {
                $date = $departure['date'];
                $availability = $departure['availability'];
                $departureType = $departure['departure_type'];
                $dateRange = explode(',', $params['date_range']);
                $travelers = $params['travelers'];

                return ($date >= date('Y-m-d', strtotime($dateRange[0])) &&
                        $date <= date('Y-m-d', strtotime($dateRange[1])) &&
                        $availability >= $travelers &&
                        $departureType == "guaranteed");
            });

            return ['items' => array_values($filteredDepartures)];
        } catch (\Exception $e) {
            Log::error('Error fetching departures for tour ' . $params['tourId'], ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public static function getDeparture($params)
    {
        $accessToken = self::getAccessToken();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        $tourId = $params['tourId'];
        $departureId = $params['departureId'];
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/departures/{$departureId}";

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getTaxonomyLanguages()
    {
        return self::taxonomyLanguages();

        $token = self::getAccessToken();
        $url = "https://api.sandbox.b2b.tourradar.com/v1/taxonomy/languages";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = Http::withHeaders($headers)->get($url);
        return $response->json();
    }

    public static function getTaxonomyDestinations($params)
    {
        $token = self::getAccessToken();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $url = "https://api.sandbox.b2b.tourradar.com/v1/taxonomy/destinations/" . $params['type'] . "?";

        if (isset($params['country_id'])) {
            $url .= "country_id=" . $params['country_id'] . "&";
        }

        if (isset($params['limit'])) {
            $url .= "limit=" . $params['limit'] . "&";
        }

        if (isset($params['page'])) {
            $url .= "page=" . $params['page'] . "&";
        }

        $response = Http::withHeaders($headers)->get($url);
        return $response->json();
    }

    public static function taxonomyLanguages()
    {
        return [
            [
                "id" => 1,
                "code" => "en",
                "name" => "English",
            ],
            [
                "id" => 2,
                "code" => "de",
                "name" => "German",
            ],
            [
                "id" => 3,
                "code" => "it",
                "name" => "Italian",
            ],
            [
                "id" => 4,
                "code" => "pt",
                "name" => "Portuguese",
            ],
            [
                "id" => 5,
                "code" => "fr",
                "name" => "French",
            ],
            [
                "id" => 6,
                "code" => "es",
                "name" => "Spanish",
            ],
            [
                "id" => 7,
                "code" => "zh",
                "name" => "Chinese",
            ],
            [
                "id" => 8,
                "code" => "nl",
                "name" => "Dutch",
            ],
            [
                "id" => 9,
                "code" => "ru",
                "name" => "Russian",
            ],
        ];
    }

    public static function getTour($tourId, $currency = 'USD', $user_country = '185')
    {
        $accessToken = self::getAccessToken();
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}?currency={$currency}&user_country={$user_country}";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getPriceCategoriesByTour($tourId)
    {
        $accessToken = self::getAccessToken();
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/prices";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getOperatorBookingFields($operatorId = 406)
    {
        $scope = "com.tourradar.operators/read";
        $accessToken = self::getAccessToken($scope);
        $url = "https://api.sandbox.b2b.tourradar.com/v1/operators/{$operatorId}/booking-fields";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getBookingsList()
    {
        $scope = "com.tourradar.bookings/read";
        $accessToken = self::getAccessToken($scope);
        $url = "https://api.sandbox.b2b.tourradar.com/v1/bookings";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function createNewBooking($body)
    {
        $scope = "com.tourradar.bookings/write";
        $accessToken = self::getAccessToken($scope);
        $url = "https://api.sandbox.b2b.tourradar.com/v1/bookings";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $body);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportToken(){
        return 'entro';
    }
}

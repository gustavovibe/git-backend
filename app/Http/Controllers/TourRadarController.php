<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class TourRadarController extends Controller
{

    public static function getAccessToken($scope = "com.tourradar.tours/read")
    {
        // ToDo: Move these variables to a .env file
        $clientId = 'hpg0tvme3ujrwcnd6fcyttwst8';
        $clientSecret = 'mjjqpzhg19rifw174ehlw1a56nufbvwxrcya2w4bz32dsbjf594';
        $urlToken = 'https://oauth.api.sandbox.b2b.tourradar.com/oauth2/token';
        $authorization = base64_encode($clientId . ':' . $clientSecret);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Basic " . $authorization,
        ];
        $body = [
            'grant_type' => 'client_credentials',
            'scope' => $scope,
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
}

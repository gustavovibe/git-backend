<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;
use App\Helpers\FormatTour;
use Illuminate\Support\Facades\Validator;

class TourRadarController extends Controller
{
    public static function getDeparturesByTour($accessToken, $params)
    {
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
            $response = $response->json();
            $response['items'] = self::formatDeparturesResponse($response['items']);
            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function formatDeparturesResponse($departures)
    {
        $response = [];
        foreach ($departures as $departure) {
            $departure['prices'] = self::formatDeparturePrices($departure['prices']);
            $departure['guide_languages'] = self::getGuideLanguagesForDeparture($departure);
            unset($departure['links']);
            array_push($response, $departure);
        }
        return $response;
    }

    public function getGuideLanguagesForDeparture($departure)
    {
        $response = [];
        $taxonomy_languages = self::getTaxonomyLanguages();
        foreach ($departure['guide_languages'] as $languageId) {
            foreach ($taxonomy_languages as $language) {
                if ($language['id'] === $languageId) {
                    array_push($response, $language);
                    break;
                }
            }
        }
        return $response;
    }

    public function formatDeparturePrices($prices)
    {
        $response = [];
        $response['based_on'] = $prices['based_on'];
        $response['price_total'] = $prices['price_total'];
        $response['mandatory_addons'] = [];
        $response['mandatory_addons'] = $prices['mandatory_addons'];
        return $response;
    }

    public static function getAccessToken()
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
            'scope' => 'com.tourradar.tours/read',
        ];

        try {
            $response = Http::withHeaders($headers)->asForm()->post($urlToken, $body);
            $data = $response->json();
            return $data['access_token'];
        } catch (RequestException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getTour($accessToken, $tourId, $currency = 'USD', $user_country = '185')
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}?currency={$currency}&user_country={$user_country}";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            $tour = $response->json();
            $tour = FormatTour::formatTourData($tour);
            return $tour;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getPriceCategoriesByTour($accessToken, $tourId)
    {
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

    public function getTaxonomyLanguages()
    {
        $token = self::getAccessToken();
        $url = "https://api.sandbox.b2b.tourradar.com/v1/taxonomy/languages";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

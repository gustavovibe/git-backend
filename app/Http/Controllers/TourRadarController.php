<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;

class TourRadarController extends Controller
{
    public function show($id)
    {
        $response = [];
        $token = $this->getAccessToken();
        $tour = $this->getTour($token, $id);
        // $response['departures'] = $this->getDeparturesByTour($token, $id);
        // $response['priceCategories'] = $this->getPriceCategoriesByTour($token, $id);
        return ApiResponse::success($tour);
    }

    private function getAccessToken()
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

    private function getTour($accessToken, $tourId, $currency = 'USD', $user_country = '185')
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}?currency={$currency}&user_country={$user_country}";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            $tour = $response->json();
            $tour = $this->formatTourData($tour);
            return $tour;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatTourData($tour)
    {
        $formatedTour = [];
        $formatedTour['tour_id'] = $tour['tour_id'];
        $formatedTour['tour_name'] = $tour['tour_name'];
        $formatedTour['reviews_count'] = $tour['reviews_count'];
        $formatedTour['tour_length_days'] = $tour['tour_length_days'];
        $formatedTour['images'] = $tour['images'];
        $formatedTour['tour_types'] = $tour['tour_types'];
        $formatedTour['age_range'] = $tour['age_range'];
        $formatedTour['max_group_size'] = $tour['max_group_size'];
        $formatedTour['guide_languages'] = $tour['guide_languages'];
        $formatedTour['start_city'] = $tour['start_city'];
        $formatedTour['end_city'] = $tour['end_city'];
        $formatedTour['destinations'] = $tour['destinations'];
        $formatedTour['prices'] = $tour['prices'];
        $formatedTour['description'] = $tour['description'];
        $formatedTour['itinerary'] = $tour['itinerary'];
        $formatedTour['services'] = $tour['services'];
        return $formatedTour;
    }

    private function getDeparturesByTour($accessToken, $tourId)
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/departures";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            $data = $response->json();
            return $data['items']; // This response is paginated
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getPriceCategoriesByTour($accessToken, $tourId)
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/prices";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            $data = $response->json();
            return $data['price_categories'];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

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
        $response['tour'] = $this->getTour($token, $id);
        $response['departures'] = $this->getDeparturesByTour($token, $id);
        $response['priceCategories'] = $this->getPriceCategoriesByTour($token, $id);
        return ApiResponse::success($response);
    }

    private function getAccessToken()
    {
        // ToDo: Move these variables to a .env file
        $clientId = 'hpg0tvme3ujrwcnd6fcyttwst8';
        $clientSecret = 'mjjqpzhg19rifw174ehlw1a56nufbvwxrcya2w4bz32dsbjf594';
        $urlToken = 'https://oauth.api.sandbox.b2b.tourradar.com/oauth2/token';
        $authorization = base64_encode($clientId . ':' . $clientSecret);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $authorization,
            ])->asForm()->post($urlToken, [
                'grant_type' => 'client_credentials',
                'scope' => 'com.tourradar.tours/read',
            ]);
            $data = $response->json();
            return $data['access_token'];
        } catch (RequestException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getTour($accessToken, $tourId, $currency = 'USD', $user_country = '185')
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}?currency={$currency}&user_country={$user_country}";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($url);

            $tour = $response->json();
            return $tour;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getDeparturesByTour($accessToken, $tourId)
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/departures";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($url);

            $data = $response->json();
            return $data['items']; // This response is paginated
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getPriceCategoriesByTour($accessToken, $tourId)
    {
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/prices";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($url);

            $data = $response->json();
            return $data['price_categories'];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

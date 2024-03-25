<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;

class TourRadarController extends Controller
{
    public function show($id)
    {
        $token = $this->getAccessToken();
        $tour = $this->getTour($token, $id);
        return ApiResponse::success($tour);
    }

    public function prices(Request $request)
    {
        $rules = [
            'tourId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $token = $this->getAccessToken();
        $response = $this->getPriceCategoriesByTour($token, $request['tourId']);
        return ApiResponse::success($response);
    }

    public function departures(Request $request)
    {
        $rules = [
            'tourId' => 'required',
            'currency' => 'sometimes|in:AUD,CAD,EUR,GBP,NZD,USD',
            'page' => 'sometimes',
            'user_country' => 'nullable|integer|min:0',
            'date_range' => 'sometimes|regex:/^\d{8}-\d{8}$/',
        ];
        $messages = [
            'tourId.required' => 'El campo :attribute es obligatorio.',
            'currency.in' => 'El campo :attribute debe ser uno de los siguientes valores: AUD, CAD, EUR, GBP, NZD, USD.',
            'date_range.regex' => 'El campo :attribute debe tener el formato YYYYMMDD-YYYYMMDD.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $token = $this->getAccessToken();
        $response = $this->getDeparturesByTour($token, $request->all());
        return ApiResponse::success($response);
    }

    private function getDeparturesByTour($accessToken, $params)
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
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
        $formatedTour['overview'] = $tour['description'];
        $formatedTour['ratings'] = $this->getRatings($tour);
        $formatedTour['reviews_count'] = $tour['reviews_count'];
        $formatedTour['tour_length_days'] = $tour['tour_length_days'];
        $formatedTour['max_group_size'] = $tour['max_group_size'];
        $formatedTour['images'] = $this->getFormattedImages($tour);
        $formatedTour['guiding_method'] = $this->getGuidingMethod($tour);
        $formatedTour['tour_type'] = $this->getTourType($tour);
        $formatedTour['tour_types'] = $tour['tour_types'];
        $formatedTour['age_range_formatted'] = $this->getAgeRange($tour);
        $formatedTour['age_range'] = $tour['age_range'];
        $formatedTour['guide_languages'] = $this->getGuideLanguages($tour);
        $formatedTour['start_city'] = $tour['start_city'];
        $formatedTour['end_city'] = $tour['end_city'];
        $formatedTour['destinations'] = $tour['destinations'];
        $formatedTour['prices'] = $this->getFormattedPrice($tour);
        $formatedTour['itinerary'] = $tour['itinerary'];
        $formatedTour['services'] = $this->getServices($tour);
        $formatedTour['operator'] = $tour['operator'];

        return $formatedTour;
    }

    private function getFormattedPrice($tour)
    {
        $response = [];
        $response['based_on'] = $tour['prices']['based_on'];
        $response['price_total'] = $tour['prices']['price_total'];
        $response['mandatory_addons'] = [];
        $response['mandatory_addons'] = $tour['prices']['mandatory_addons'];
        return $response;
    }

    private function getGuideLanguages($tour)
    {
        $response = [];
        $taxonomy_languages = $this->getTaxonomyLanguages();
        foreach ($tour['guide_languages'] as $languageId) {
            foreach ($taxonomy_languages as $language) {
                if ($language['id'] === $languageId) {
                    array_push($response, $language);
                    break;
                }
            }
        }
        return $response;
    }

    private function getTaxonomyLanguages()
    {
        $token = $this->getAccessToken();
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

    private function getAgeRange($tour)
    {
        $min = $tour['age_range']['strict']['min_age'];
        $max = $tour['age_range']['strict']['max_age'];
        return "{$min}-{$max}";
    }

    private function getTourType($tour)
    {
        $formatted = [];
        foreach ($tour['tour_types'] as $tourType) {
            if ($tourType['group_id'] === 1) {
                array_push($formatted, $tourType);
            }
        }
        return $formatted;
    }

    private function getServices($tour)
    {
        $included = [];
        $excluded = [];
        foreach ($tour['services'] as $serviceName => $serviceDetails) {
            if (count($serviceDetails) === 0) {
                continue;
            }
            foreach ($serviceDetails as $service) {
                if ($service['is_included']) {
                    $included[$serviceName] = [];
                    array_push($included[$serviceName], $service);
                } else {
                    $excluded[$serviceName] = [];
                    array_push($excluded[$serviceName], $service);
                }
            }
        }
        $response = [];
        $response['included'] = $included;
        $response['excluded'] = $excluded;
        return $response;
    }

    private function getGuidingMethod($tour)
    {
        $formatted = [];
        foreach ($tour['tour_types'] as $tourType) {
            if ($tourType['group_id'] === 2) {
                array_push($formatted, $tourType);
            }
        }
        return $formatted;
    }

    private function getRatings($tour)
    {
        if (isset($tour['ratings']['overall'])) {
            return $tour['ratings']['overall'];
        } else {
            return $tour['ratings']['operator'];
        }
    }

    private function getFormattedImages($tour)
    {
        return array_map(function ($image) {
            return $image['url'];
        }, $tour['images']);
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
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

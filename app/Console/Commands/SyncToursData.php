<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Tour;
use App\Models\City;
use App\Models\TourCity;
use App\Models\Country;
use App\Models\TourCountry;
use App\Models\NaturalDestination;
use App\Models\TourNaturalDestination;
use App\Models\tourType;
use App\Models\tour_tourType;

class SyncToursData extends Command
{
    protected $signature = 'sync:tours';
    protected $description = 'Sync tours data from the API to the database';
    private $token;

    public function handle()
    {
        $this->token = $this->asyncGetAccessToken();

        $startPage = 101;
        $endPage = 200;

        for ($currentPage = $startPage; $currentPage <= $endPage; $currentPage++) {
            $tours = $this->fetchDataFromApi($currentPage)['items'] ?? [];

            $this->info("Processing page {$currentPage}. Number of tours: " . count($tours));

            if (!empty($tours)) {
                foreach ($tours as $tourData) {
                    $this->saveTourToDatabase($tourData);
                }
                $this->info("Synced data for page {$currentPage}");
            } else {
                $this->info("No more data on page {$currentPage}");
            }
            sleep(3);
        }
    }

    private function asyncGetAccessToken()
    {
        try {
            $clientId = "2gmdq5q758vtiwxxwxgwse5whv";
            $clientSecret = "cz3p1gnwatvepzdrpw7b68uyxizte2noabkslo1ue5gkm3lmu97";

            $response = Http::withHeaders([
				'Accept-Language' => 'en',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("$clientId:$clientSecret"),
            ])
                ->asForm()
                ->post('https://oauth.api.sandbox.b2b.tourradar.com/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'scope' => 'com.tourradar.tours/read',
                ]);

            $accessToken = $response->json('access_token');
            return $accessToken;

        } catch (\Exception $e) {
            $this->error('Error fetching access token: ' . $e->getMessage());
            throw $e;
        }
    }

    private function fetchDataFromApi($currentPage)
    {
        try {
            $response = Http::withToken($this->token)
                ->get('https://api.sandbox.b2b.tourradar.com/v1/tours/search', [
                    'sort_order' => 'asc',
                    'limit' => 10,
                    'sort_by' => 'price',
                    'currency' => 'USD',
                    'user_country' => 185,
                    'is_instant_confirmable' => true,
                    'page' => $currentPage,
                ]);

            $responseData = $response->json();
            $tours = $responseData['items'] ?? [];
            $this->line("Response for page {$currentPage}: Number of tours: " . count($tours));

            if (isset($responseData['error'])) {
                $this->error("API Error on page {$currentPage}: " . json_encode($responseData['error']));
                return [];
            }

            return $responseData;
        } catch (\Exception $e) {
            $this->error('Error fetching data from API: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getDeparturesByTour($tourId)
    {
        // Delay of 1 second
        usleep(1000000); // 1 second in microseconds

        $accessToken = $this->token;
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        $url = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/departures?date_range=20240801-20250801&user_country=185&currency=USD";

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            if ($e->getCode() == 504) {
                $this->error("Error fetching departures for tour {$tourId}: Request failed with status code 504. Continuing to the next tour.");
            } else {
                $this->error('Error fetching departures for tour ' . $tourId . ': ' . $e->getMessage());
            }
            return [];
        }
    }

private function saveTourToDatabase($tourData)
{
    try {
        $images = $tourData['images'] ?? [];
        $image = collect($images)->firstWhere('type', 'image');
        $mapImage = collect($images)->firstWhere('type', 'map');

        // Fetch departures data
        $departuresData = $this->getDeparturesByTour($tourData['tour_id']);
        $departuresItems = $departuresData['items'] ?? [];
        $departureStatus = 'not_guaranteed';

        foreach ($departuresItems as $departure) {
            if ($departure['departure_type'] === 'guaranteed') {
                $departureStatus = 'guaranteed';
                break;
            }
        }

        //if ($departureStatus !== 'guaranteed') {
        //    $this->info("Tour {$tourData['tour_id']} does not have any guaranteed departures. Skipping...");
        //    return;
        //}

        $tour = Tour::updateOrCreate(
            ['tour_id' => $tourData['tour_id']],
            [
                'tour_name' => $tourData['tour_name'] ?? null,
                'locale' => $tourData['locale'] ?? null,
                'language' => $tourData['language'] ?? null,
                'is_active' => $tourData['is_active'] ?? false,
                'tour_length_days' => $tourData['tour_length_days'] ?? null,
                'start_city' => $tourData['start_city']['location_id'] ?? null,
                'end_city' => $tourData['end_city']['location_id'] ?? null,
                'is_instant_confirmable' => $tourData['is_instant_confirmable'] ?? false,
                'price_total' => $tourData['prices']['price_total'] ?? null,
                'price_currency' => $tourData['prices']['currency'] ?? null,
                'price_promotion' => $tourData['prices']['promotion']['discount'] ?? null,
                'reviews_count' => $tourData['reviews_count'] ?? null,
                'ratings_overall' => $tourData['ratings']['overall'] ?? null,
                'ratings_operator' => $tourData['ratings']['operator'] ?? null,
                'description' => substr($tourData['description'] ?? '', 0, 150),
                'min_age' => $tourData['age_range']['strict']['min_age'] ?? null,
                'max_age' => $tourData['age_range']['strict']['max_age'] ?? null,
                'max_group_size' => $tourData['max_group_size'] ?? null,
                'main_image' => $image['url'] ?? null,
                'main_thumbnail' => $image['thumbnail_url'] ?? null,
                'map_image' => $mapImage['url'] ?? null,
                'map_thumbnail' => $mapImage['thumbnail_url'] ?? null,
                'departures' => $departureStatus,
				'operator_id' => $tourData['operator']['id'] ?? null,
				'operator_name' => $tourData['operator']['name'] ?? null,
                'commission' => $tourData['prices']['partner_info']['commission_rate'] ?? null
            ]
        );

        $this->info("Saved tour: {$tourData['tour_id']} - {$tourData['tour_name']}");

        // Save related cities
        if (isset($tourData['destinations']['cities'])) {
            $this->saveCitiesToDatabase($tourData['destinations']['cities'], $tour->tour_id);
        }
		
		// Save related countries
        if (isset($tourData['destinations']['countries'])) {
            $this->saveCountriesToDatabase($tourData['destinations']['countries'], $tour->tour_id);
        }
		// Save related natural destinations
        if (isset($tourData['destinations']['natural_destinations'])) {
            $this->saveNaturalsToDatabase($tourData['destinations']['natural_destinations'], $tour->tour_id);
        }
		
		// Save related tour types
        if (isset($tourData['tour_types'])) {
            $this->saveTypesToDatabase($tourData['tour_types'], $tour->tour_id);
        }

    } catch (\Exception $e) {
        $this->error("Error saving tour: {$tourData['tour_id']} - {$e->getMessage()}");
    }
}

private function saveCitiesToDatabase($cities, $tourId)
{
    $this->info("Saving cities for tour ID: {$tourId}");

    foreach ($cities as $cityData) {
        try {
            // Check if city exists, if not, create it
            $city = City::updateOrCreate(
                ['t_city_id' => $cityData['location_id']],
                [
                    'city_name' => $cityData['city_name'],
                    't_country_id' => $cityData['country_code']
                ]
            );

            // Log city object to debug
            $this->info("City object: " . json_encode($city));

            if ($tourId && $city->t_city_id) {
                // Attach the city to the tour
                TourCity::updateOrCreate(
                    ['tour_id' => $tourId, 't_city_id' => $city->t_city_id],
                );

                $this->info("Saved city: {$cityData['location_id']} - {$cityData['city_name']} for tour: {$tourId}");
            } else {
                $this->error("Tour or City not found for IDs: Tour ID - {$tourId}, City ID - {$city->t_city_id}");
            }
        } catch (\Exception $e) {
            $this->error("Error saving city: {$cityData['location_id']} - {$e->getMessage()}");
        }
    }
}
	
private function saveCountriesToDatabase($countries, $tourId)
{
    $this->info("Saving countries for tour ID: {$tourId}");

    foreach ($countries as $countryData) {
        try {
            // Check if country exists, if not, create it
            $country = country::updateOrCreate(
                ['t_country_id' => $countryData['location_id']],
                [
                    'name' => $countryData['country_name'],
                    'country_code' => $countryData['country_code']
                ]
            );

            // Log country object to debug
            $this->info("country object: " . json_encode($country));

            if ($tourId && $country->t_country_id) {
                // Attach the country to the tour
                Tourcountry::updateOrCreate(
                    ['tour_id' => $tourId, 't_country_id' => $country->t_country_id],
                );

                $this->info("Saved country: {$countryData['location_id']} - {$countryData['country_name']} for tour: {$tourId}");
            } else {
                $this->error("Tour or country not found for IDs: Tour ID - {$tourId}, country ID - {$country->t_country_id}");
            }
        } catch (\Exception $e) {
            $this->error("Error saving country: {$countryData['location_id']} - {$e->getMessage()}");
        }
    }
}
	
private function saveNaturalsToDatabase($natural_destinations, $tourId)
{
    $this->info("Saving natural_destinations for tour ID: {$tourId}");

    foreach ($natural_destinations as $naturals) {
        try {
            // Check if NaturalDestination exists, if not, create it
            $NaturalDestination = NaturalDestination::updateOrCreate(
                ['t_natural_id' => $naturals['location_id']],
                [
                    'destination_name' => $naturals['natural_destination_name'],
                    'destination_type' => $naturals['type']
                ]
            );

            // Log NaturalDestination object to debug
            $this->info("NaturalDestination object: " . json_encode($NaturalDestination));

            if ($tourId && $NaturalDestination->t_natural_id) {
                // Attach the NaturalDestination to the tour
                TourNaturalDestination::updateOrCreate(
                    ['tour_id' => $tourId, 't_natural_id' => $NaturalDestination->t_natural_id],
                );

                $this->info("Saved NaturalDestination: {$naturals['location_id']} - {$naturals['natural_destination_name']} for tour: {$tourId}");
            } else {
                $this->error("Tour or NaturalDestination not found for IDs: Tour ID - {$tourId}, NaturalDestination ID - {$NaturalDestination->t_natural_id}");
            }
        } catch (\Exception $e) {
            $this->error("Error saving NaturalDestination: {$naturals['location_id']} - {$e->getMessage()}");
        }
    }
}	

private function saveTypesToDatabase($types, $tourId)
{
    $this->info("Saving types for tour ID: {$tourId}");

    foreach ($types as $typeData) {
        try {
            // Check if type exists, if not, create it
            $type = tourType::updateOrCreate(
                ['tour_type_id' => $typeData['type_id']],
                [
                    'tourtype_name' => $typeData['type_name'],
                    'group_id' => $typeData['group_id'],
                    'group_name' => $typeData['group_name']
                ]
            );

            // Log type object to debug
            $this->info("type object: " . json_encode($type));

            if ($tourId && $type->tour_type_id) {
                // Attach the type to the tour
                tour_Tourtype::updateOrCreate(
                    ['tour_id' => $tourId, 'tour_type_id' => $type->tour_type_id],
                );

                $this->info("Saved type: {$typeData['type_id']} - {$typeData['type_name']} for tour: {$tourId}");
            } else {
                $this->error("Tour or type not found for IDs: Tour ID - {$tourId}, type ID - {$type->tour_type_id}");
            }
        } catch (\Exception $e) {
            $this->error("Error saving type: {$typeData['type_id']} - {$e->getMessage()}");
        }
    }
}   
	
}

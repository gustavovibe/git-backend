<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;
use App\Filters\ToursFilters;
use App\Models\Tour;

class TourRadarController extends Controller
{

    /**
     * Get access token.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $scope Scope
     * @return array     
     */
    public static function getAccessToken()
    {

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
                'com.tourradar.operators/read',
                'com.tourradar.bookings/read',
                'com.tourradar.bookings/write'
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

    /**
     * Get departures by tour.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $params Params
     * @return array     
     */
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

    /**
     * Get multiple departures by tours.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function getMultipleDeparturesByTours(Request $request)
    {
       try{
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

        $tourIds = array_slice($tourIds, $start, $itemsPerPage);

        foreach ($tourIds as $tourId) {
            $params['tourId'] = $tourId;
            $params['page'] = 1; // Always fetch first page of departures for each tourId
            $response = $this->getDeparturesByTourParams($params);
            Log::info('API response from getDeparturesByTourParams', ['tourId' => $tourId, 'response' => $response]);

            if (isset($response['items'])) {
                // Ensure every departure gets the correct tour_id key
                foreach ($response['items'] as &$departure) {
                    $departure['tour_id'] = $tourId;
                }
                
                $cheapestDeparture = null;
                foreach ($response['items'] as $departure) {
                    if (isset($departure['prices']['price_total'])) {
                        $priceTotal = $departure['prices']['price_total'];
                        if ($cheapestDeparture === null || $priceTotal < $cheapestDeparture['prices']['price_total']) {
                            $cheapestDeparture = $departure;
                        }
                    }
                }
                if ($cheapestDeparture !== null) {
                    $departures[] = $cheapestDeparture;
                }
            }
            sleep(0.1); // delay between API calls
        }
        

       // Log::info('Returning departures', ['departures' => $departures]);

        return response()->json(['items' => $departures]);
       }catch(Exception $e){
        return response()->json(['status'=>false,'response'=>$e->getMessagge()]);
       }
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
            $dateRange = explode(',', $params['date_range']);
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

           // Log::info('API Response for Tour ' . $params['tourId'] . ':', $responseBody);

            if (!isset($responseBody['items'])) {
                return ['error' => 'Unexpected response structure', 'response' => $responseBody];
            }

            $departures = $responseBody['items'];
            Log::info('Departures: ', $departures);
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
            Log::info('Filtered departures: ', $filteredDepartures);
            // Fetch additional departure details for each item
            $departuresWithDetails = array_map(function ($departure) use ($params) {
                $departureDetails = self::getDeparture([
                    'tourId' => $params['tourId'],
                    'departureId' => $departure['id']
                ]);
                $departure['departures'] = $departureDetails;
    
                // Process accommodations to select the cheapest valid one based on travelers
                if (
                    isset($departureDetails['prices']['accommodations']) &&
                    is_array($departureDetails['prices']['accommodations'])
                ) {
                    $accommodations = $departureDetails['prices']['accommodations'];
                    $travelers = $params['travelers'];
    
                    $validAccommodations = array_filter($accommodations, function ($acc) use ($travelers) {
                        if ($travelers === 1) {
                            return $acc['beds_number'] === 1;
                        }
                        // For multiple travelers, check if the traveler count divides evenly by the beds number
                        return $travelers % $acc['beds_number'] === 0;
                    });
    
                    if (!empty($validAccommodations)) {
                        // Choose the cheapest accommodation (assuming price is in 'value')
                        $cheapest = array_reduce($validAccommodations, function ($prev, $curr) {
                            return ($prev === null || $curr['value'] < $prev['value']) ? $curr : $prev;
                        }, null);
                        $departure['cheapestAccommodation'] = $cheapest;
                    } else {
                        $departure['cheapestAccommodation'] = null;
                    }
                } else {
                    $departure['cheapestAccommodation'] = null;
                }
    
                return $departure;
            }, array_values($filteredDepartures));
            Log::info('Filtered departures with details: ', $filteredDepartures);

           $groupedDepartures = [];
            foreach ($departuresWithDetails as $departure) {
                $tourId = $departure['tour_id'] ?? null;
                if ($tourId) {
                    $groupedDepartures[$tourId][] = $departure;
                }
            }
            Log::info('Grouped departures by tour_id', ['groupedDepartures' => $groupedDepartures]);
            // Extract unique tour_ids from the grouped departures
            $tourIds = array_keys($groupedDepartures);

            // Query the local database for tours matching these tour_ids, including relationships
            $dbTours = \App\Models\Tour::with(['cities', 'natural_destination', 'type', 'countries'])
                ->whereIn('tour_id', $tourIds)
                ->get()
                ->toArray();

            // Merge the departures into each tour record under a new key "departure"
            $toursWithDepartures = array_map(function ($tour) use ($groupedDepartures) {
                $tourId = $tour['tour_id'];
                // Add the departures array for this tour (if exists)
                $tour['departure'] = $groupedDepartures[$tourId] ?? [];
                return $tour;
            }, $dbTours);
            Log::info('Tours after merging departures', ['toursWithDepartures' => $toursWithDepartures]);
            // Sort the tours by reviews_count (desc) and ratings_overall (desc)
            usort($toursWithDepartures, function ($a, $b) {
                $reviewsDiff = ($b['reviews_count'] ?? 0) - ($a['reviews_count'] ?? 0);
                if ($reviewsDiff === 0) {
                    return floatval($b['ratings_overall'] ?? 0) - floatval($a['ratings_overall'] ?? 0);
                }
                return $reviewsDiff;
            });
            Log::info('Tours after sorting', ['toursWithDepartures' => $toursWithDepartures]);
            // Mark the top 3 tours as best_seller; the rest as false
            foreach ($toursWithDepartures as $index => &$tour) {
                $tour['best_seller'] = $index < 3;
            }
            Log::info('Final tours with best_seller flag', ['toursWithDepartures' => $toursWithDepartures]);
            return ['items' => $toursWithDepartures];

        } catch (\Exception $e) {
           // Log::error('Error fetching departures for tour ' . $params['tourId'], ['error' => $e->getMessage()]);
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
        // First, get the departure information
        $response = Http::withHeaders($headers)->get($url);
        $departureData = $response->json();

        // Check if accommodations and price_tiers exist and are not empty
        if (isset($departureData['prices']['accommodations'])) {
            foreach ($departureData['prices']['accommodations'] as &$accommodation) {
                if (!empty($accommodation['price_tiers'])) {
                    // Make an API call to fetch the prices information
                    $priceUrl = "https://api.sandbox.b2b.tourradar.com/v1/tours/{$tourId}/prices";
                    $priceResponse = Http::withHeaders($headers)->get($priceUrl);
                    $priceData = $priceResponse->json();

                    // Create a mapping of price category by id for quick lookup
                    $priceCategoryMap = [];
                    foreach ($priceData['price_categories'] as $priceCategory) {
                        $priceCategoryMap[$priceCategory['id']] = $priceCategory;
                    }

                    // Update the price tiers with the matching category information
                    foreach ($accommodation['price_tiers'] as &$priceTier) {
                        $priceCategoryId = $priceTier['price_category_id'];
                        if (isset($priceCategoryMap[$priceCategoryId])) {
                            $priceCategoryInfo = $priceCategoryMap[$priceCategoryId];
                            $priceTier['age_min'] = $priceCategoryInfo['age_min'];
                            $priceTier['age_max'] = $priceCategoryInfo['age_max'];
                            $priceTier['external_reference'] = $priceCategoryInfo['external_reference'];
                        }
                    }
                }
            }
        }

        return $departureData;
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
            $response = Http::withHeaders($headers)->retry(1, 100)->post($url, $body); // Allow only 1 attempt
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function checkBooking($id)
    {
        $scope = "com.tourradar.bookings/read";
        $accessToken = self::getAccessToken($scope);
        $url = "https://api.sandbox.b2b.tourradar.com/v1/bookings/{$id}";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url); // Perform a GET request
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportToken(){
        return 'entro';
    }
}

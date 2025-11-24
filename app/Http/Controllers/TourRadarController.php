<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;
use App\Models\Tour;
use App\Models\Departure;
use Carbon\Carbon;
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
        Log::info('getMultipleDeparturesByTours called with:', $request->all());
    
        try {
            $params = $request->all();
            if (!isset($params['tourIds'])) {
                return response()->json(['error' => 'tourIds parameter is required'], 400);
            }
    
            $tourIds = explode(',', $params['tourIds']);
    
            $departures = [];                 // <- declare once, outside loops
            $itemsPerPage = 10;
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $start = ($page - 1) * $itemsPerPage;
    
            // slice the incoming tourIds according to page
            $paginatedTourIds = array_slice($tourIds, $start, $itemsPerPage);
    
            foreach ($paginatedTourIds as $tourId) {
                $params['tourId'] = $tourId;
                $tour = Tour::where('tour_id', $tourId)->first();
                if (! $tour) {
                    Log::info("Skipping missing tour", ['tourId' => $tourId]);
                    continue;
                }
    
                // Always fetch first page of departures for each tourId (keeps original behavior)
                $params['page'] = 1;
    
                Log::info("Tour $tourId attributes:", $tour->getAttributes());
    
                // prices may be stored as casted attribute
                Log::info(
                    "DB price_categories for tour $tourId",
                    ['raw' => $tour->getAttributes()['prices'] ?? null, 'cast' => $tour->prices ?? []]
                );
    
                $cats = $tour->prices ?? [];
                // determine child age bounds (keeps your original logic)
                $childCat = collect($cats)->firstWhere('external_reference', 'child');
    
                if ($childCat) {
                    $childMin = $childCat['age_min']  ?? 0;
                    $childMax = $childCat['age_max']  ?? 18;
                } elseif (count($cats) === 1 && ($cats[0]['external_reference'] ?? '') === 'adult') {
                    $adult = $cats[0];
                    if ($adult['age_min'] !== null || $adult['age_max'] !== null) {
                        $childMin = $adult['age_min'] ?? 0;
                        $childMax = 18;
                    } else {
                        $childMin = $tour->min_age ?? 0;
                        $childMax = 18;
                    }
                } else {
                    $childMin = $tour->min_age ?? 0;
                    $childMax = 18;
                }
    
                // validate children ages (if any)
                $childrenAgesRaw = $request->input('childrenAges');
                $childrenAges = $childrenAgesRaw ? array_map('intval', explode(',', $childrenAgesRaw)) : [];
                foreach ($childrenAges as $age) {
                    if ($age < $childMin || $age > $childMax) {
                        Log::info("Skipping tour $tourId: child age $age not within [$childMin,$childMax]");
                        continue 2; // skip entire tour
                    }
                }
                Log::info("Tour $tourId child age range: [$childMin,$childMax] | ChildrenAges: " . implode(',', $childrenAges));
    
                // call your existing helper to get departures for this tour
                $response = $this->getDeparturesByTourParamsV2($params);
    
                if (isset($response['items']) && is_array($response['items'])) {
                    Log::info('Departures found for tour', ['tourId' => $tourId, 'departures_count' => count($response['items'])]);
    
                    $items = $response['items'];
    
                    // find cheapest value among the items (if any)
                    $minValue = null;
                    foreach ($items as $d) {
                        if (isset($d['cheapestAccommodation']['value'])) {
                            $v = $d['cheapestAccommodation']['value'];
                            if ($minValue === null || $v < $minValue) {
                                $minValue = $v;
                            }
                        }
                    }
    
                    // Append ALL departures, annotating each with tourId, price categories, and cheapest flag
                    foreach ($items as $departure) {
                        // attach tourId and price categories
                        $departure['tourId'] = $tourId;
                        $departure['price_categories'] = $cats ?? [];
    
                        // mark cheapest(s)
                        $departure['is_cheapest'] = false;
                        if ($minValue !== null
                            && isset($departure['cheapestAccommodation']['value'])
                            && $departure['cheapestAccommodation']['value'] == $minValue) {
                            $departure['is_cheapest'] = true;
                        }
    
                        // append single item
                        $departures[] = $departure;
                    }
    
                    Log::info('Added departures for tour', [
                        'tourId' => $tourId,
                        'added' => count($items),
                        'total_so_far' => count($departures)
                    ]);
                } else {
                    Log::info('No departures found for tour', ['tourId' => $tourId]);
                }
    
                // if you want a short fractional delay between external calls use usleep (microseconds)
                usleep(100000); // 0.1 second
            } // end foreach paginatedTourIds
    
            // optional: dedupe final departures by id (prevents duplicates if returned multiple times)
            $byId = [];
            foreach ($departures as $d) {
                // prefer numeric id key; fallback to combination if missing
                if (isset($d['id'])) {
                    $byId[(string)$d['id']] = $d;
                } else {
                    // fallback - generate a unique hash if there's no id (rare)
                    $byId[md5(json_encode($d))] = $d;
                }
            }
            $departures = array_values($byId);
    
            Log::info('Final departures count before return', ['count' => count($departures)]);
    
            return response()->json(['items' => $departures]);
        } catch (Exception $e) {
            Log::error('Error in getMultipleDeparturesByTours', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'response' => $e->getMessage()], 500);
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
                        // Ensure 'beds_number' is set and greater than 0
                        if (!isset($acc['beds_number']) || $acc['beds_number'] <= 0) {
                            return false;
                        }
                    
                        // Check if the accommodation is shared or not
                        $isShared = isset($acc['is_shared']) ? $acc['is_shared'] : false; // Default to false if not set
                    
                        // If only one traveler, consider 'is_shared' condition
                        if ($travelers === 1) {
                            return $isShared || $acc['beds_number'] === 1;
                        }
                    
                        // For multiple travelers, ensure beds_number divides evenly into travelers
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
            

            $filteredDeparturesWithAccommodations = array_filter($departuresWithDetails, function ($departure) {
                return !empty($departure['cheapestAccommodation']); // Keep only departures with a valid accommodation
            });
            
            return ['items' => array_values($filteredDeparturesWithAccommodations)];
            //return ['items' => $departuresWithDetails];

        } catch (\Exception $e) {
           // Log::error('Error fetching departures for tour ' . $params['tourId'], ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
      
    private function getDeparturesByTourParamsV2($params)
    {
        // Validate date_range parameter (expected format: "YYYY/MM/DD,YYYY/MM/DD")
        if (empty($params['date_range'])) {
            return ['error' => 'Missing date_range parameter'];
        }
        
        $dateRange = explode(',', $params['date_range']);
        if (count($dateRange) !== 2) {
            return ['error' => 'Invalid date_range format. It should be "YYYY/MM/DD,YYYY/MM/DD".'];
        }
        
        // Format start and end dates to 'Y-m-d'
        $startDate = date('Y-m-d', strtotime($dateRange[0]));
        $endDate   = date('Y-m-d', strtotime($dateRange[1]));
        
        // Validate the start date is not before a minimum allowed date
        $now = Carbon::now()->format('Y-m-d');
        if (strtotime($startDate) < strtotime($now)) {
            return ['error' => 'Start date of the date range must be greater than or equal to 2024-05-25'];
        }
        
        // Determine the number of travelers (default to 1 if not provided)
        $travelers = isset($params['travelers']) ? $params['travelers'] : 1;
        
        // Retrieve departures from the local DB for the given tour_id, within the date range,
        // with sufficient availability and departure_type "guaranteed"
        $departures = \App\Models\Departure::where('tour_id', $params['tourId'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('availability', '>=', $travelers)
            ->where('departure_type', 'guaranteed')
            ->get();
        
        // If no departures are found, return an empty items array.
        if ($departures->isEmpty()) {
            return ['items' => []];
        }
        
        Log::info('Departures found for tour', [$params['tourId'], 'departures' => $departures->toArray()]);
        
        // Process each departure to add detailed information and choose a valid, cheapest accommodation.
        $departuresWithDetails = array_map(function ($departure) use ($params, $travelers) {
            // Fetch additional departure details from API using departure id.
            $departureDetails = self::getDeparture([
                'tourId'      => $params['tourId'],
                'departureId' => $departure['id']
            ]);
            $departure['departures'] = $departureDetails;
            
            Log::info('Departures found for departure', [
                'id' => $departure['id'],
                'details' => $departureDetails,
            ]);               

            // Initialize cheapest accommodation as null.
            $departure['cheapestAccommodation'] = null;
            
            // If the API response has accommodations data, process it.
            if (isset($departureDetails['prices']['accommodations']) && is_array($departureDetails['prices']['accommodations'])) {
                $accommodations = $departureDetails['prices']['accommodations'];
        
                // Filter out accommodations that do not meet traveler requirements.
                $validAccommodations = array_filter($accommodations, function ($acc) use ($travelers) {
                    // Check if 'beds_number' is valid.
                    if (!isset($acc['beds_number']) || $acc['beds_number'] <= 0) {
                        return false;
                    }
        
                    $isShared = isset($acc['is_shared']) ? $acc['is_shared'] : false;
        
                    // For one traveler, accept if it's a shared accommodation or has exactly one bed.
                    if ($travelers === 1) {
                        return $isShared || $acc['beds_number'] === 1;
                    }
        
                    // For multiple travelers, the number of travelers must be evenly divisible by the beds number.
                    return ($travelers % $acc['beds_number'] === 0);
                });
        
                // If valid accommodations exist, choose the cheapest one (based on the 'value' field).
                if (!empty($validAccommodations)) {
                    $cheapest = array_reduce($validAccommodations, function ($prev, $curr) {
                        return ($prev === null || $curr['value'] < $prev['value']) ? $curr : $prev;
                    }, null);
                    $departure['cheapestAccommodation'] = $cheapest;
                }
            }
        
            return $departure;
        }, $departures->toArray());
        
        // Filter to include only departures with a valid cheapest accommodation.
        $filteredDepartures = array_filter($departuresWithDetails, function ($departure) {
            return !empty($departure['cheapestAccommodation']);
        });
        Log::info('$filteredDepartures', $filteredDepartures);
        return ['items' => array_values($filteredDepartures)];
    }
     
    public function getMultipleDeparturesOnlyDb(Request $request)
    {
        Log::info('getMultipleDeparturesOnlyDb called with:', $request->all());
    
        try {
            $params = $request->all();
            if (!isset($params['tourIds'])) {
                return response()->json(['error' => 'tourIds parameter is required'], 400);
            }
    
            $tourIds = explode(',', $params['tourIds']);
    
            $departures = [];
            $itemsPerPage = 10;
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $start = ($page - 1) * $itemsPerPage;
    
            // only process the chunk of tourIds for this page
            $paginatedTourIds = array_slice($tourIds, $start, $itemsPerPage);
    
            $travelersGlobal = isset($params['travelers']) ? (int)$params['travelers'] : 1;
    
            foreach ($paginatedTourIds as $tourId) {
                $params['tourId'] = $tourId;
                $tour = Tour::where('tour_id', $tourId)->first();
                if (! $tour) {
                    Log::info("Tour not found, skipping", ['tourId' => $tourId]);
                    continue;
                }
    
                // pass travelers as param (so getDeparturesByTourOnlyDb could use it if needed)
                $params['travelers'] = $travelersGlobal;
                $departureDetails = $this->getDeparturesByTourOnlyDb($params);
                $items = $departureDetails['items'] ?? [];
    
                if (empty($items)) {
                    Log::info('No departures returned from DB for tour', ['tourId' => $tourId]);
                    continue;
                }
    
                $tourDepartures = [];
                $cheapestDeparture = null;
    
                foreach ($items as $item) {
                    // normalize price_total
                    $priceTotal = isset($item['price_total']) ? (float)$item['price_total'] : null;
    
                    // normalize accommodations (could be null, json string, or array)
                    $accommodationsRaw = $item['accommodations'] ?? null;
                    $accommodations = [];
    
                    if ($accommodationsRaw) {
                        if (is_string($accommodationsRaw)) {
                            $decoded = json_decode($accommodationsRaw, true);
                            $accommodations = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($accommodationsRaw)) {
                            $accommodations = $accommodationsRaw;
                        }
                    }
    
                    // filter valid accommodations according to travelers
                    $travelers = $travelersGlobal;
                    $validAccommodations = array_filter($accommodations, function ($acc) use ($travelers) {
                        if (!isset($acc['beds_number']) || $acc['beds_number'] <= 0) {
                            return false;
                        }
                        $isShared = isset($acc['is_shared']) ? (bool)$acc['is_shared'] : false;
    
                        if ($travelers === 1) {
                            return $isShared || ($acc['beds_number'] === 1);
                        }
    
                        // for multiple travelers, require that beds_number divides travelers evenly
                        return ($travelers % $acc['beds_number'] === 0);
                    });
    
                    // find cheapest accommodation by 'value' (if any)
                    $cheapestAccommodation = null;
                    if (!empty($validAccommodations)) {
                        usort($validAccommodations, function ($a, $b) {
                            $va = isset($a['value']) ? floatval($a['value']) : INF;
                            $vb = isset($b['value']) ? floatval($b['value']) : INF;
                            return $va <=> $vb;
                        });
                        $cheapestAccommodation = $validAccommodations[0];
                    }
    
                    // attach computed fields to each departure item
                    $item['valid_accommodations'] = array_values($validAccommodations);
                    $item['cheapest_accommodation'] = $cheapestAccommodation;
    
                    $tourDepartures[] = $item;
    
                    // track cheapest departure by price_total
                    if ($priceTotal !== null) {
                        if ($cheapestDeparture === null || $priceTotal < (float)($cheapestDeparture['price_total'] ?? INF)) {
                            $cheapestDeparture = $item;
                        }
                    }
                }
    
                $departures[] = [
                    'tour_id' => $tourId,
                    'tour' => $tour->toArray(),
                    'departures' => $tourDepartures,
                    'cheapest_departure' => $cheapestDeparture
                ];
            }
    
            Log::info('Returning departures', ['departures' => $departures]);
    
            return response()->json(['items' => $departures], 200);
    
        } catch(Exception $e) {
            Log::error('getMultipleDeparturesOnlyDb error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'response' => $e->getMessage()], 500);
        }
    }
       

    private function getDeparturesByTourOnlyDb($params)
    {
        // Validate date_range parameter (expected format: "YYYY/MM/DD,YYYY/MM/DD")
        if (empty($params['date_range'])) {
            return ['error' => 'Missing date_range parameter'];
        }
        
        $dateRange = explode(',', $params['date_range']);
        if (count($dateRange) !== 2) {
            return ['error' => 'Invalid date_range format. It should be "YYYY/MM/DD,YYYY/MM/DD".'];
        }
        
        // Format start and end dates to 'Y-m-d'
        $startDate = date('Y-m-d', strtotime($dateRange[0]));
        $endDate   = date('Y-m-d', strtotime($dateRange[1]));
        
        // Validate the start date is not before a minimum allowed date
        $now = Carbon::now()->format('Y-m-d');
        if (strtotime($startDate) < strtotime($now)) {
            return ['error' => 'Start date of the date range must be greater than or equal to 2024-05-25'];
        }
        
        // Determine the number of travelers (default to 1 if not provided)
        $travelers = isset($params['travelers']) ? $params['travelers'] : 1;
        
        // Retrieve departures from the local DB for the given tour_id, within the date range,
        // with sufficient availability and departure_type "guaranteed"
        $departures = \App\Models\Departure::where('tour_id', $params['tourId'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('availability', '>=', $travelers)
            ->where('departure_type', 'guaranteed')
            ->get();
        
        // If no departures are found, return an empty items array.
        if ($departures->isEmpty()) {
            return ['items' => []];
        }
        
        Log::info('Departures found for tour', [$params['tourId'], 'departures' => $departures->toArray()]);
        
        return ['items' => $departures->toArray()];
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
            
    /*        
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
    */
            return $departureData;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public static function getDeparturedb($params)
    {
        $departureId = $params['departureId'];
        $departure = Departure::where('id', $departureId)->first();
        return $departure;
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
            $upstream = $response->json() ?? [];

            $priceCategories = $upstream['data']['price_categories']
                ?? $upstream['price_categories']
                ?? $upstream['original']['price_categories']
                ?? [];

            $tour = Tour::where('tour_id', $tourId)->first();
            $tourAges = [
                'min_age' => $tour ? (is_null($tour->min_age) ? null : (int)$tour->min_age) : null,
                'max_age' => $tour ? (is_null($tour->max_age) ? null : (int)$tour->max_age) : null,
            ];

            // **RETURN AN ARRAY** — caller will JSON-encode it
            return [
                    'price_categories' => $priceCategories,
                    'tour_ages' => $tourAges,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
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
        $url = "https://api.sandbox.b2b.tourradar.com/v1/bookings/{$id}/status";
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

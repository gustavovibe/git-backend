<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\ActionLog;
use App\Models\Order;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class DuffelApiController extends Controller
{

    public function getAirline(Request $request, $id = null)
    {
        // allow either path param (/airlines/{id}) or query ?id=...
        if (empty($id)) {
            $id = $request->query('id');
        }

        if (empty($id)) {
            return response()->json(['error' => 'Airline ID is required (path or ?id=)'], 400);
        }

        // optional: basic validation (adjust regex if you know exact format)
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            $headers = self::getHeaders();
            $url = 'https://api.duffel.com/air/airlines/' . urlencode($id);

            // forward request to Duffel
            $response = Http::withHeaders($headers)->get($url);

            // return Duffel JSON with original status code
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // api/duffel/create-request-get-offers
    public function createRequestGetOffers(Request $request)

    {
        // Validating params
        $validator = $this->validateParamsWhenDuffelRequest($request);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        // Outbound slice
        $slices = [
            [
                'origin' => $request->origin,
                'destination' => $request->destination,
                'departure_date' => $request->departureDate,
            ]
        ];

        // Add inbound slice (optional)
        $shouldAddSecondSlice = $request->has('originInbound') && $request->has('destinationInbound') && $request->has('departureDateInbound');
        if ($shouldAddSecondSlice) {
            $inboundSlice = [
                'origin' => $request->originInbound,
                'destination' => $request->destinationInbound,
                'departure_date' => $request->departureDateInbound,
            ];
            array_push($slices, $inboundSlice);
        }

        // Getting passengers
        $passengers = $this->getPassengers($request);

        try {
            // Construct the request body
            $requestBody = [
                'data' => [
                    'slices' => $slices,
                    'passengers' => $passengers,
                    'cabin_class' => $request->cabinClass ?? null
                ]
            ];

            // Getting headers
            $headers = self::getHeaders();

            $url = 'https://api.duffel.com/air/offer_requests?'; // default url
            $url = $this->addMoreQueryparamsToUrl($url, $request);

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->post($url, $requestBody);

            $response = $response->json();

            // Filter offers
            if (isset($response['data']['offers'])) {
                $response['data']['offers'] = $this->handleOffers($response['data']['offers'], $request);
                /*
                    Note:
                    This offers cannot be paginated because the request is always new, but
                    you can paginate when asking for request by its id
                */
            }

            return $response;
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // api/duffel/get-request-by-id
    public function getRequestById(Request $request)
    {
        // Check if the 'page' and 'perPage' parameters are sent
        if ($request->has('page') && $request->has('perPage')) {
            // If both parameters are sent, set $request->limit to null
            $request->request->remove('limit');
        }

        // Validating params
        $validator = $this->validateParamsWhenRequestById($request);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            // Getting Headers
            $headers = self::getHeaders();

            // Building url
            $url = 'https://api.duffel.com/air/offer_requests/' . $request->requestId;

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            $response = $response->json();

            // Filtering offers
            if (isset($response['data']['offers'])) {
                $response['data']['offers'] = $this->handleOffers($response['data']['offers'], $request);
                // Pagination
                $response['data'] = $this->paginateOffers($response['data'], $request);
            }
            return $response;
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // api/duffel/get-offer-by-id
    public function getOfferById(Request $request)
    {
        // Validations
        $validator = $this->validateParamsWhenOfferById($request);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            // Getting Headers
            $headers = self::getHeaders();

            // Building url
            $url = 'https://api.duffel.com/air/offers/' . $request->offerId;
            
            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get offer by ID.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $offerId Offer ID
     * @return array     
     */
    public function getOffer(Request $request)
    {
        $offerId = $request->query('offerId');
    
        if (empty($offerId)) {
            return response()->json(['error' => 'Offer ID is required'], 400);
        }
    
        try {
            $headers = self::getHeaders();
    
            $url = 'https://api.duffel.com/air/offers'
                 . '?offer_request_id=' . urlencode($offerId);
    
            $url = $this->addMoreOfferParamsToUrl($url, $request);
    
            // ✅ Log the final URL and headers
            \Log::info('[Duffel] Final URL and Headers', [
                'url'     => $url,
                'headers' => $headers,
            ]);
    
            $response = Http::withHeaders($headers)->get($url);
            $responseData = $response->json();
    
            // 1) Compute baggage counts
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                foreach ($responseData['data'] as &$offer) {
                    $checkedByPassenger      = [];
                    $carryByPassenger        = [];
                    $segmentCountByPassenger = [];
                
                    // 1) Walk every slice → segment → passenger
                    foreach ($offer['slices'] as $slice) {
                        foreach ($slice['segments'] as $segment) {
                            foreach ($segment['passengers'] as $passenger) {
                                // identify the passenger
                                $pid = $passenger['passenger_id'] ?? $passenger['id'];
                
                                // init counters if first time we see this passenger
                                if (! isset($checkedByPassenger[$pid])) {
                                    $checkedByPassenger[$pid]      = 0;
                                    $carryByPassenger[$pid]        = 0;
                                    $segmentCountByPassenger[$pid] = 0;
                                }
                
                                // increment the segment‐count
                                $segmentCountByPassenger[$pid]++;
                
                                // sum all bags for this passenger in this segment
                                foreach ($passenger['baggages'] ?? [] as $bag) {
                                    if ($bag['type'] === 'checked') {
                                        $checkedByPassenger[$pid] += (int) $bag['quantity'];
                                    } elseif ($bag['type'] === 'carry_on') {
                                        $carryByPassenger[$pid]   += (int) $bag['quantity'];
                                    }
                                }
                            }
                        }
                    }
                
                    // 2) For each passenger, compute “bags per segment”
                    $perChecked = [];
                    $perCarry   = [];
                    foreach ($segmentCountByPassenger as $pid => $segCount) {
                        // avoid division by zero
                        if ($segCount > 0) {
                            // integer division gives you the allowance per passenger
                            $perChecked[$pid] = intdiv($checkedByPassenger[$pid], $segCount);
                            $perCarry  [$pid] = intdiv($carryByPassenger  [$pid], $segCount);
                        } else {
                            $perChecked[$pid] = 0;
                            $perCarry  [$pid] = 0;
                        }
                    }
                
                    // 3) Finally, pick the minimum across all passengers
                    if (! empty($perChecked)) {
                        $offer['baggage_checked'] = min($perChecked);
                        $offer['baggage_carry']   = min($perCarry);
                    } else {
                        $offer['baggage_checked'] = 0;
                        $offer['baggage_carry']   = 0;
                    }
                }
                unset($offer);
                
            }
    
            // ← NEW: grab filter params (if any)
            $filterChecked = $request->get('baggage_checked');
            $filterCarry   = $request->get('baggage_carry');
    
            // ← NEW: filter out offers that don’t meet the criteria
            if ($filterChecked !== null || $filterCarry !== null) {
                $responseData['data'] = array_values(array_filter(
                    $responseData['data'],
                    function ($offer) use ($filterChecked, $filterCarry) {
                        if ($filterChecked !== null && $offer['baggage_checked'] < (int) $filterChecked) {
                            return false;
                        }
                        if ($filterCarry   !== null && $offer['baggage_carry']   < (int) $filterCarry) {
                            return false;
                        }
                        return true;
                    }
                ));
            }

            $limit = (int) $request->get('limit', 3);
            $page  = max(1, (int) $request->get('page', 1));

            $totalOffers = count($responseData['data']);
            $totalPages  = (int) ceil($totalOffers / $limit);

            $responseData['data'] = array_slice(
                $responseData['data'],
                ($page - 1) * $limit,
                $limit
            );

            $responseData['meta'] = [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $totalOffers,
                'totalPages' => $totalPages,
            ];

            return response()->json($responseData, $response->status());

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    /**
     * Get seats.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function getSeats(Request $request)
    {
        // Validations
        $validator = $this->validateParamsWhenOfferById($request);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            // Getting Headers
            $headers = self::getHeaders();

            // Building URL with the offer ID as a query parameter
            $url = 'https://api.duffel.com/air/seat_maps?offer_id=' . $request->offerId;

            // Log the request URL and headers for debugging
            // Log::info('Request URL: ' . $url);
            // Log::info('Request Headers: ', $headers);

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create new booking.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $body Body
     * @return array     
     */
    public static function createNewBooking($body)
    {
        $headers = self::getHeaders();

        $url = 'https://api.duffel.com/air/orders';
        // Make the request to the Duffel API
        $response = Http::withHeaders($headers)->post($url, $body);

        return $response->json();
    }

    /**
     * Pay booking.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $body Body
     * @return array     
     */
    public static function payBooking($body)
    {
        $headers = self::getHeaders();

        $url = 'https://api.duffel.com/air/payments';
        // Make the request to the Duffel API
        $response = Http::withHeaders($headers)->post($url, $body);

        return $response->json();
    }

    /**
     * Add seats.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function addSeats(Request $request)
    {
        $offerId = $request->query('offerId');
        $amount = $request->query('amount');
        $serviceId = $request->query('serviceId');

        $headers = self::getHeaders();

        $response = Http::withHeaders($headers)->post("https://api.duffel.com/air/orders/{$offerId}/services", [
            'data' => [
                'payment' => [
                    'type' => 'balance',
                    'currency' => 'USD',
                    'amount' => $amount,
                ],
                'add_services' => [
                    [
                        'quantity' => 1,
                        'id' => $serviceId
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Service added successfully', 'data' => $response->json()]);
        } else {
            return response()->json(['message' => 'Failed to add service', 'error' => $response->json()], $response->status());
        }
    }

    /**
     * Get order by ID.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function getOrderById(Request $request)
    {
        // Validations
        $validator = $this->validateParamsWhenOrderById($request);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        try {
            // Getting Headers
            $headers = self::getHeaders();

            // Building url
            $url = 'https://api.duffel.com/air/orders/' . $request->orderId;

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // private functions
    private function getFilteredOffers($offers, $offersQuantity, $request)
    {
        $offers = $this->getOffersWithoutDuffelAirways($offers, $offersQuantity);
        $offers = $this->validateOffers($offers, $offersQuantity, $request);

        return $offers;
    }

    private function getOffersWithoutDuffelAirways($offers, $offersQuantity)
    {
        $filteredOffers = [];
        $count = 0; // Variable to keep track of filtered offers count

        // Iterate over the offers
        foreach ($offers as $offer) {
            if ($count >= $offersQuantity) {
                break; // Exit the loop if we've already reached the required quantity
            }

            $skipOffer = false; // Variable to determine whether to skip this offer

            foreach ($offer['slices'] as $slice) {
                if (!isset($slice['segments'])) {
                    continue; // Skip this section if 'segments' is absent
                }

                foreach ($slice['segments'] as $segment) {
                    if (!isset($segment['operating_carrier']['name'])) {
                        continue; // Skip this segment if 'operating_carrier' or 'name' is absent
                    }

                    if ($segment['operating_carrier']['name'] === 'Duffel Airways') {
                        $skipOffer = true; // Set the flag to skip this offer
                        break 2; // Exit the nested loops
                    }
                }
            }

            if (!$skipOffer) {
                $filteredOffers[] = $offer; // Add the offer if it shouldn't be skipped
                $count++; // Increment the count of filtered offers
            }
        }

        return $filteredOffers;
    }

    private function validateParamsWhenRequestById($request)
    {
        $rules = [
            'requestId' => 'required|string|regex:/^orq_.+$/',
            'limit' => 'sometimes|integer|min:1', // Limit the quantity of offers
            'page' => 'required_with:perPage|integer|min:1', // the pagination will ignore 'limit'
            'perPage' => 'required_with:page|integer|min:1', // the pagination will ignore 'limit'
            'minimumCheckedBaggage' => 'sometimes|integer|min:1',
            'minimumCabinBaggage' => 'sometimes|integer|min:1',
            'stops' => 'required|string|in:any,direct,upToOneStop,upToTwoStops',
            'sortByLeastExpensive' => 'sometimes',
            'sortByLeastDuration' => 'sometimes',
        ];

        $messages = [
            'requestId.regex' => "El campo 'requestId' debe comenzar con 'orq_'",
            'page.required_with' => "El parámetro 'page' es obligatorio cuando se envía el parámetro 'perPage'",
            'perPage.required_with' => "El parámetro 'perPage' es obligatorio cuando se envía el parámetro 'page'",
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function getPassengers($request)
    {
        $one_adult = ['type' => 'adult'];
        $one_child = ['type' => 'child'];

        if (!$request->has('adultsCount')) {
            return [$one_adult]; // Default
        }

        $passengers = [];
        for ($i = 0; $i < $request->adultsCount; $i++) {
            array_push($passengers, $one_adult);
        }

        // adding children
        if ($request->has('childrenCount')) {
            $childrenCount = $request->input('childrenCount', 0);
            $childrenAgesRaw = $request->input('childrenAges');
            $childrenAges = $childrenAgesRaw ? array_map('intval', explode(',', $childrenAgesRaw)) : [];

            for ($i = 0; $i < $childrenCount; $i++) {
                if (isset($childrenAges[$i])) {
                    // Duﬀel wants “age” for under-18 passengers
                    $passengers[] = ['age' => (int) $childrenAges[$i]];
                } else {
                    // Fallback to the old “child” type
                    $passengers[] = ['type' => 'child'];
                }
            }
        }     

        return $passengers;
    }

    private function addMoreQueryparamsToUrl($url, $request)
    {
        $default_supplierTimeout = 5000;
        $default_limit = 5;
        $default_sort = "total_amount";
        $default_maxConnections = 1;

        if ($request->has('supplierTimeout')) {
            $url .= "&supplier_timeout=" . $request->supplierTimeout . "&";
        } else {
            $url .= "&supplier_timeout=" . $default_supplierTimeout . "&";
        }

        if ($request->has('limit')) {
            $url .= "&limit=" . $request->limit . "&";
        } else {
            $url .= "&limit=" . $default_limit . "&";
        }

        if ($request->has('sort')) {
            $url .= "&sort=" . $request->sort . "&";
        } else {
            $url .= "&sort=" . $default_sort . "&";
        }

        if ($request->has('maxConnections')) {
            $url .= "&max_connections=" . $request->maxConnections . "&";
        } else {
            $url .= "&max_connections=" . $default_maxConnections . "&";
        }

        return $url;
    }

    private function addMoreOfferParamsToUrl(string $url, Request $request): string
    {
        $default_limit          = 100;
        $default_sort           = 'total_amount';
        $default_maxConnections = 1;
    
        if ($request->has('after')) {
            $url .= '&after=' . urlencode($request->after);
        }
    
        if ($request->has('before')) {
            $url .= '&before=' . urlencode($request->before);
        }
    
       // $url .= '&limit='. urlencode($request->get('limit', $default_limit));
    
        $url .= '&sort='
             . urlencode($request->get('sort', $default_sort));
    
        $url .= '&max_connections='
             . urlencode($request->get('maxConnections', $default_maxConnections));
    
        return $url;
    }
    
      

    private function validateOffers($offers, $offersQuantity, $request)
    {
        $validatedOffers = [];
        $count = 0; // Variable to keep track of filtered offers count

        // Iterate over the offers
        foreach ($offers as $offer) {
            if ($count >= $offersQuantity) {
                break; // Exit the loop if we've already reached the required quantity
            }

            $isValidOffer = $this->validateBaggages($offer, $request);
            if (!$isValidOffer) {
                continue;
            }

            if ($request->has('stops')) {
                $isValidOffer = $this->validateStops($offer, $request);
                if (!$isValidOffer) {
                    continue;
                }
            }

            if ($request->has('payment')) {
            $isValidOffer = $this->validatePayment($offer, $request);
                if (!$isValidOffer) {
                    continue;
                }
            }

            if ($request->has('airlines')) {
            $isValidOffer = $this->validateAirlines($offer, $request);
                if (!$isValidOffer) {
                    continue;
                }
            }

            $validatedOffers[] = $offer; // Add the offer if it has baggage
            $count++; // Increment the count of baggage offers
        }

        $validatedOffers = $this->sortOffers($validatedOffers, $request);
        return $validatedOffers;
    }


    private function calculateTotalFlightTime($offers)
    {
        $totalMinutes = 0;
    
        foreach ($offers as $offer) {
            if (isset($offer['slices']) && is_array($offer['slices'])) {
                foreach ($offer['slices'] as $slice) {
                    if (isset($slice['duration'])) {
                        // Parse ISO 8601 duration format (e.g., PT5H30M)
                        $interval = CarbonInterval::fromString($slice['duration']);
                        $totalMinutes += $interval->totalMinutes;
                    }
                }
            }
        }
    
        return [
            'totalMinutes' => $totalMinutes,
        ];
    }

    private function sortOffers($offers, $request)
    {
        $newOffers = $offers; // Initially, the new offers will be a copy of the original array

        if ($request->has('sortByLeastExpensive')) {
            // Define a comparison function to sort by base_amount
            $compareOffers = function ($a, $b) {
                return floatval($a['base_amount']) <=> floatval($b['base_amount']);
            };

            // Sort the offers by base_amount in ascending order
            usort($newOffers, $compareOffers);
        }

        if ($request->has('sortByLeastDuration')) { 
            // Define a comparison function to sort by total flight time
            $compareOffers = function ($a, $b) {
                $totalTimeA = $this->calculateTotalFlightTime([$a])['totalMinutes'];
                $totalTimeB = $this->calculateTotalFlightTime([$b])['totalMinutes'];
        
                return $totalTimeA <=> $totalTimeB; // Sort in ascending order
            };
        
            // Sort the offers by total flight time
            usort($newOffers, $compareOffers);
        }
        
        return $newOffers;
    }


    private function validateParamsWhenOfferById($request)
    {
        $rules = [
            'offerId' => 'required|string|regex:/^off_.+$/',
        ];
        $messages = [
            'offerId.regex' => 'El campo :attribute debe comenzar diciendo "off_".',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function validateParamsWhenOrderById($request)
    {
        $rules = [
            'orderId' => 'required|string|regex:/^ord_.+$/',
        ];
        $messages = [
            'orderId.regex' => 'El campo :attribute debe comenzar diciendo "ord_".',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function validateBaggages($offer, $request)
    {
        foreach ($offer['slices'] as $slice) {
            foreach ($slice['segments'] as $segment) {
                foreach ($segment['passengers'] as $passenger) {
                    $isValidOffer = false;
                    $hasCheckedBaggage = true; // default
                    $hasCabinBaggage = true; // default

                    $checkedBaggages = array_filter($passenger['baggages'], function ($baggage) {
                        return $baggage['type'] === 'checked';
                    });
                    $cabinBaggages = array_filter($passenger['baggages'], function ($baggage) {
                        return $baggage['type'] === 'carry_on';
                    });

                    // baggages included?
                    if (!empty($checkedBaggages) || !empty($cabinBaggages)) {
                        $isValidOffer = true;

                        // minimum checked baggage included?
                        if ($request->has('minimumCheckedBaggage')) {
                            $hasCheckedBaggage = false;
                            $minimumCheckedBaggage = intval($request->get('minimumCheckedBaggage'));

                            if (!empty($checkedBaggages)) {
                                $checkedBaggage = reset($checkedBaggages);
                                if ($checkedBaggage['quantity'] >= $minimumCheckedBaggage) {
                                    $hasCheckedBaggage = true;
                                }
                            }
                        }

                        // minimum cabin baggage included?
                        if ($request->has('minimumCabinBaggage')) {
                            $hasCabinBaggage = false;
                            $minimumCabinBaggage = intval($request->get('minimumCabinBaggage'));

                            if (!empty($cabinBaggages)) {
                                $cabinBaggage = reset($cabinBaggages);
                                if ($cabinBaggage['quantity'] >= $minimumCabinBaggage) {
                                    $hasCabinBaggage = true;
                                }
                            }
                        }
                    }

                    if (!$isValidOffer || !$hasCheckedBaggage || !$hasCabinBaggage) {
                        return false;
                    }
                }
            }
        }

        // All 'checked' baggages meet the minimum quantity requirement
        return true;
    }
    
    private function validatePayment($offer, $request) 
    {
        // If payment requirement is "any," automatically pass validation
        if ($request->get('payment') === 'any') {
            return true;
        }
    
        // Extract payment requirements from the offer
        $paymentRequirements = $offer['payment_requirements'];
    
        // Handle case where instant payment is not allowed
        if ($request->get('payment') === 'false') {
            if ($paymentRequirements['requires_instant_payment'] === true) {
                return false;
            }
        }
    
        // Handle case where instant payment is required
        if ($request->get('payment') === 'true') {
            if ($paymentRequirements['requires_instant_payment'] === false) {
                return false;
            }
        }
    
        // Default to valid if no conditions are violated
        return true;
    }

    private function validateAirlines($offer, $request)
    {
        // Extract the airlines parameter from the request and split it into an array
        $requestedAirlines = explode(',', $request->get('airlines', ''));
    
        // If "any" is included in the requested airlines, automatically pass validation
        if (in_array('any', $requestedAirlines, true)) {
            return true;
        }
    
        // Extract the IATA code of the offer
        $airlineCode = $offer['owner']['iata_code'] ?? null; // Safely extract IATA code
    
        // Check if the airline's IATA code matches any in the requested list
        if (in_array($airlineCode, $requestedAirlines, true)) {
            return true; // Valid if a match is found
        }
    
        // Default to false if no match is found
        return false;
    }
    

    private function validateStops($offer, $request)
    {
        if ($request->get('stops') === 'any') {
            return true;
        }

        foreach ($offer['slices'] as $slice) {
            // only direct flights
            if ($request->get('stops') === "direct") {
                if (count($slice['segments']) != 1) {
                    return false;
                }
            }

            // direct or one stop
            if ($request->get('stops') === "upToOneStop") {
                if (count($slice['segments']) > 2) {
                    return false;
                }
            }

            // direct or one stop or two stops
            if ($request->get('stops') === "upToTwoStops") {
                if (count($slice['segments']) > 3) {
                    return false;
                }
            }
        }

        return true;
    }

    private function handleOffers($offers, $request)
    {
        $offersQuantity = $request->has('limit') ? $request->limit : count($offers);
        $offers = $this->getFilteredOffers($offers, $offersQuantity, $request);

        return $offers;
    }

    private function paginateOffers($data, $request)
    {
        if (!$request->has('page') || !$request->has('perPage')) {
            return $data;
        }

        $offers = $data['offers'];

        $perPage = intval($request->perPage);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Get the offers for the current page
        $currentOffers = array_slice($offers, $offset, $perPage);

        // Count total offers
        $totalOffers = count($offers);

        // Create a LengthAwarePaginator object to handle pagination
        $paginator = new LengthAwarePaginator($currentOffers, $totalOffers, $perPage, $page);

        // Build pagination metadata
        $paginationData = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        // Build the response array with paginated data and metadata
        $data['offers'] = $paginator->items();
        $data['offersMeta'] = $paginationData;
        return $data;
    }

    private static function getHeaders()
    {
        return [
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept' => 'application/json',
            'Duffel-Version' => 'v2',
            'Authorization' => 'Bearer duffel_test_tfNofacp8LVcPjSf7OA0Q78ghrmuoakwtBhjbxaRrs2',
        ];
    }

    private function validateParamsWhenDuffelRequest($request)
    {
        $rules = [
            'origin' => 'required',
            'destination' => 'required',
            'departureDate' => 'required',
            'originInbound' => 'sometimes',
            'destinationInbound' => 'sometimes',
            'departureDateInbound' => 'sometimes',
            'adultsCount' => 'sometimes|integer|min:1',
            'childrenCount' => 'sometimes|integer|min:0',
            'cabinClass' => 'sometimes|in:first,business,premium_economy,economy',
            'supplierTimeout' => 'sometimes',
            'limit' => 'sometimes',
            'sort' => 'sometimes',
            'maxConnections' => 'sometimes',
            'sortByLeastExpensive' => 'sometimes',
            'sortByLeastDuration' => 'sometimes',
            'childrenAges'   => 'sometimes',
            'childrenAges.*' => 'integer|min:0|max:17',
        ];
        $messages = [
            'cabinClass.in' => "El campo :attribute debe ser uno de los siguientes valores: 'first' 'business' 'premium_economy' 'economy'",
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Flight cancel.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function flightCancel(Request $r){
        try{

            $booking= Order::where('booking_id',$r->tour_id)->first();
            $order_id=$booking->duffel_id;

            $headers = self::getHeaders();

            $quote_url = "https://api.duffel.com/air/order_cancellations";

            $quote_response = Http::withHeaders($headers)->post($quote_url, [
                'data' => ['order_id' => $order_id]
            ]);
            $quote_data = $quote_response->json();

            if (isset($quote_data['data'])) {
                $data= $quote_data['data'];
                $data['expires_at']=Carbon::parse($data['expires_at'])->format('F j, Y g:i A');
                return response()->json(['success' => true, 'data' =>$data ]);
            } else {
                return response()->json(['success' => false, 'data' =>$quote_data['errors'][0]['message']]);
            }



        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    /**
     * Confirm cancel.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function confirmCancel(Request $r){
        try{
            $confirm_url = "https://api.duffel.com/air/order_cancellations/{$r->cancel_id}/actions/confirm";

            $headers = self::getHeaders();

            $confirm_response = Http::withHeaders($headers)->post($confirm_url);

            $confirm_data = $confirm_response->json();

            if (isset($confirm_data['data']['confirmed_at'])) {

                ActionLog::create([
                    'user_id' => $r->user_log,
                    'type' => 'Cancel',
                    'action' => $r->traveler_id? 'Traveler update successfully':'Traveler created successfully',
                    'item' => 'Traveler',
                ]);

                return response()->json(['success' => true, 'data' => 'Order cancelled successfully.']);
            } else {
                return response()->json(['success' => false, 'data' =>$confirm_data['errors'][0]['message']]);
            }

        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }
}


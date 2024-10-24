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

class DuffelApiController extends Controller
{
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
            Log::info('Request URL: ' . $url);
            Log::info('Request Headers: ', $headers);

            // Make the request to the Duffel API
            $response = Http::withHeaders($headers)->get($url);

            // Return the response from the Duffel API
            return $response->json();
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public static function createNewBooking($body)
    {
        $headers = self::getHeaders();

        $url = 'https://api.duffel.com/air/orders';
        // Make the request to the Duffel API
        $response = Http::withHeaders($headers)->post($url, $body);

        return $response->json();
    }

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
            for ($i = 0; $i < $request->childrenCount; $i++) {
                array_push($passengers, $one_child);
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
            $url .= "supplier_timeout=" . $request->supplierTimeout . "&";
        } else {
            $url .= "supplier_timeout=" . $default_supplierTimeout . "&";
        }

        if ($request->has('limit')) {
            $url .= "limit=" . $request->limit . "&";
        } else {
            $url .= "limit=" . $default_limit . "&";
        }

        if ($request->has('sort')) {
            $url .= "sort=" . $request->sort . "&";
        } else {
            $url .= "sort=" . $default_sort . "&";
        }

        if ($request->has('maxConnections')) {
            $url .= "max_connections=" . $request->maxConnections . "&";
        } else {
            $url .= "max_connections=" . $default_maxConnections . "&";
        }

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

            $validatedOffers[] = $offer; // Add the offer if it has baggage
            $count++; // Increment the count of baggage offers
        }

        $validatedOffers = $this->sortOffers($validatedOffers, $request);
        return $validatedOffers;
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
            'Duffel-Version' => 'v1',
            'Authorization' => 'Bearer duffel_test_sf_69EQS6KXC3-FmqSn48zmzIg3-qlrX7zQpr00n2Ho',
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
        ];
        $messages = [
            'cabinClass.in' => "El campo :attribute debe ser uno de los siguientes valores: 'first' 'business' 'premium_economy' 'economy'",
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

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

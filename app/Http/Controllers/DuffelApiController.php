<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public static function createNewBooking($body)
    {
        $headers = self::getHeaders();

        $url = 'https://api.duffel.com/air/orders';
        // Make the request to the Duffel API
        $response = Http::withHeaders($headers)->post($url, $body);

        return $response->json();
    }

    // private functions
    private function getFilteredOffers($offers, $offersQuantity, $request)
    {
        $offers = $this->getOffersWithoutDuffelAirways($offers, $offersQuantity);
        $offers = $this->getBaggageOffers($offers, $offersQuantity, $request);

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

    private function getBaggageOffers($offers, $offersQuantity, $request)
    {
        $baggageOffers = [];
        $count = 0; // Variable to keep track of filtered offers count

        // Iterate over the offers
        foreach ($offers as $offer) {
            if ($count >= $offersQuantity) {
                break; // Exit the loop if we've already reached the required quantity
            }

            $hasBaggage = $this->offerHasBaggage($offer);

            if ($request->has('minimumCheckedBaggage')) {
                $hasBaggage = $this->offerHasCheckedBaggage($offer, $request);
            }
            if ($request->has('minimumCabinBaggage')) {
                $hasBaggage = $this->offerHasCabinBaggage($offer, $request);
            }

            if ($hasBaggage) {
                $baggageOffers[] = $offer; // Add the offer if it has baggage
                $count++; // Increment the count of baggage offers
            }
        }

        return $baggageOffers;
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

    private function offerHasBaggage($offer)
    {
        foreach ($offer['slices'] as $slice) {
            if (!isset($slice['segments'])) {
                continue; // Skip this slice if 'segments' key is missing
            }

            foreach ($slice['segments'] as $segment) {
                if (!isset($segment['passengers'])) {
                    continue; // Skip this segment if 'passengers' key is missing
                }

                foreach ($segment['passengers'] as $passenger) {
                    if (!isset($passenger['baggages'])) {
                        continue; // Skip this passenger if 'baggages' key is missing
                    }

                    foreach ($passenger['baggages'] as $baggage) {
                        if ($baggage['type'] === 'checked' || $baggage['type'] === 'carry_on') {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function offerHasCheckedBaggage($offer, $request)
    {
        $minimumCheckedBaggage = $request->get('minimumCheckedBaggage');

        // each slice is an inbound or an outbound
        foreach ($offer['slices'] as $slice) {

            foreach ($slice['segments'] as $segment) {

                $segmentHasCheckedBaggage = false;
                foreach ($segment['passengers'] as $passenger) {
                    foreach ($passenger['baggages'] as $baggage) {
                        if ($baggage['type'] === 'checked') {
                            if ($baggage['quantity'] >= $minimumCheckedBaggage) {
                                $segmentHasCheckedBaggage = true;
                            } else {
                                return false; // Found a 'checked' baggage that does not meet the minimum quantity. Offer is invalid
                            }
                        }
                    }
                }

                // If no 'checked' baggage was found in this segment, the offer is invalid
                if (!$segmentHasCheckedBaggage) {
                    return false;
                }
            }
        }

        // All 'checked' baggages meet the minimum quantity requirement
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
            'Accept-Encoding' => 'gzip',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
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
        ];
        $messages = [
            'cabinClass.in' => "El campo :attribute debe ser uno de los siguientes valores: 'first' 'business' 'premium_economy' 'economy'",
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function offerHasCabinBaggage($offer, $request)
    {
        $minimumCabinBaggage = $request->get('minimumCabinBaggage');

        foreach ($offer['slices'] as $slice) {

            foreach ($slice['segments'] as $segment) {

                $segmentHasCabinBaggage = false;
                foreach ($segment['passengers'] as $passenger) {
                    foreach ($passenger['baggages'] as $baggage) {
                        if ($baggage['type'] === 'carry_on') {
                            if ($baggage['quantity'] >= $minimumCabinBaggage) {
                                $segmentHasCabinBaggage = true;
                            } else {
                                return false; // Found a 'cabin' baggage that does not meet the minimum quantity. Offer is invalid
                            }
                        }
                    }
                }

                // If no 'cabin' baggage was found in this segment, the offer is invalid
                if (!$segmentHasCabinBaggage) {
                    return false;
                }
            }
        }

        // All 'cabin' baggages meet the minimum quantity requirement
        return true;
    }
}

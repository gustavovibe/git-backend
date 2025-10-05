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

    // small helper to validate HH:MM
    $isValidTime = function ($t) {
        return is_string($t) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
    };

    // build a slice helper with ability to skip time filters when $ignoreTimes = true
    $buildSlice = function ($origin, $destination, $departureDate, $prefix = '', $ignoreTimes = false) use ($request, $isValidTime) {
        $slice = [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departureDate,
        ];

        if (!$ignoreTimes) {
            // departure_time
            $depFrom = $request->get("departureTimeFrom{$prefix}");
            $depTo   = $request->get("departureTimeTo{$prefix}");
            $departureTime = [];
            if ($depFrom !== null) {
                if (!$isValidTime($depFrom)) {
                    throw new \InvalidArgumentException("departureTimeFrom{$prefix} must be in HH:MM format");
                }
                $departureTime['from'] = $depFrom;
            }
            if ($depTo !== null) {
                if (!$isValidTime($depTo)) {
                    throw new \InvalidArgumentException("departureTimeTo{$prefix} must be in HH:MM format");
                }
                $departureTime['to'] = $depTo;
            }
            if (!empty($departureTime)) {
                $slice['departure_time'] = $departureTime;
            }

            // arrival_time
            $arrFrom = $request->get("arrivalTimeFrom{$prefix}");
            $arrTo   = $request->get("arrivalTimeTo{$prefix}");
            $arrivalTime = [];
            if ($arrFrom !== null) {
                if (!$isValidTime($arrFrom)) {
                    throw new \InvalidArgumentException("arrivalTimeFrom{$prefix} must be in HH:MM format");
                }
                $arrivalTime['from'] = $arrFrom;
            }
            if ($arrTo !== null) {
                if (!$isValidTime($arrTo)) {
                    throw new \InvalidArgumentException("arrivalTimeTo{$prefix} must be in HH:MM format");
                }
                $arrivalTime['to'] = $arrTo;
            }
            if (!empty($arrivalTime)) {
                $slice['arrival_time'] = $arrivalTime;
            }
        }

        return $slice;
    };
    
    $ignoreOutboundTimes = false;
    /* Decide whether to ignore outbound times based on tourDate
    if ($request->filled('tourDate') && $request->filled('departureDate')) {
        try {
            // Accept tourDate in dd-mm-yyyy or yyyy-mm-dd
            $tourDateRaw = $request->get('tourDate');
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tourDateRaw)) {
                $tourDate = \Carbon\Carbon::createFromFormat('d-m-Y', $tourDateRaw)->startOfDay();
            } else {
                // try ISO or other parseable format
                $tourDate = \Carbon\Carbon::parse($tourDateRaw)->startOfDay();
            }

            $outboundDate = \Carbon\Carbon::parse($request->departureDate)->startOfDay();

            // If departureDate is BEFORE the tourDate, ignore outbound times in the first request
            if ($outboundDate < $tourDate) {
                $ignoreOutboundTimes = true;
                \Log::info("tourDate logic: outbound departure {$outboundDate->toDateString()} is before tourDate {$tourDate->toDateString()} — ignoring outbound time filters for initial Duffel request.");
            }
        } catch (\Exception $ex) {
            // If parsing fails, don't change behavior; log for debug
            \Log::warning('Could not parse tourDate or departureDate: ' . $ex->getMessage());
        }
    }
    */
    // Decide whether to ignore outbound times based on tourDate
    $ignoreOutboundTimes = false;
    if ($request->filled('tourDate') && $request->filled('departureDate')) {
        try {
            // Accept tourDate in dd-mm-yyyy or yyyy-mm-dd
            $tourDateRaw = $request->get('tourDate');
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tourDateRaw)) {
                $tourDate = \Carbon\Carbon::createFromFormat('d-m-Y', $tourDateRaw)->startOfDay();
            } else {
                // try ISO or other parseable format
                $tourDate = \Carbon\Carbon::parse($tourDateRaw)->startOfDay();
            }

            $outboundDate = \Carbon\Carbon::parse($request->departureDate)->startOfDay();

            // If departureDate is BEFORE the tourDate, ignore outbound times in the first request
            if ($outboundDate < $tourDate) {
                $ignoreOutboundTimes = true;
                \Log::info("tourDate logic: outbound departure {$outboundDate->toDateString()} is before tourDate {$tourDate->toDateString()} — ignoring outbound time filters for initial Duffel request.");
            }
        } catch (\Exception $ex) {
            // If parsing fails, don't change behavior; log for debug
            \Log::warning('Could not parse tourDate or departureDate: ' . $ex->getMessage());
        }
    }

    // Outbound slice
    try {
        // Build outbound, possibly ignoring times for outbound slice per tourDate logic
        $slices = [
            $buildSlice($request->origin, $request->destination, $request->departureDate, '', $ignoreOutboundTimes)
        ];

        // Add inbound slice (optional) — inbound times still follow request params
        $shouldAddSecondSlice = $request->has('originInbound') && $request->has('destinationInbound') && $request->has('departureDateInbound');
        if ($shouldAddSecondSlice) {
            $slices[] = $buildSlice($request->originInbound, $request->destinationInbound, $request->departureDateInbound, 'Inbound', false);
        }

        // Getting passengers
        $passengers = $this->getPassengers($request);

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

        // --- LOGGING (optional) ---
        try {
            $logHeaders = $headers;
            if (isset($logHeaders['Authorization'])) {
                $logHeaders['Authorization'] = preg_replace('/Bearer\s+(.+)/i', 'Bearer ****', $logHeaders['Authorization']);
            }
            \Log::debug('Duffel request URL: ' . $url);
            \Log::debug('Duffel request headers: ' . json_encode($logHeaders, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            \Log::info('Duffel request body: ' . json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $logEx) {
            \Log::error('Failed to log Duffel request details: ' . $logEx->getMessage());
        }

        // Make the request to the Duffel API (first attempt)
        $httpResponse = Http::withHeaders($headers)->post($url, $requestBody);
        try {
            \Log::debug('Duffel response status: ' . $httpResponse->status());
            //\Log::debug('Duffel response body: ' . $httpResponse->body());
        } catch (\Exception $logEx) {
            \Log::error('Failed to log Duffel response: ' . $logEx->getMessage());
        }

        $response = $httpResponse->json();
        $time = 1;
        // Filter offers (note: server-side time checks still run based on request params)
        if (isset($response['data']['offers'])) {
            $response['data']['offers'] = $this->handleOffers($response['data']['offers'], $request, $time);
        }

        // --- If no offers, do one adjusted-date retry (only once) ---
        $alreadyAdjusted = $request->get('adjusted_search', false);
        if (
            (empty($response['data']['offers'] ?? []) || count($response['data']['offers']) === 0)
            && !$alreadyAdjusted
            && $request->filled('departureDate')
            && ($shouldAddSecondSlice ? $request->filled('departureDateInbound') : true)
        ) {
            try {
                // compute new dates using Carbon
                $departureDate = \Carbon\Carbon::parse($request->departureDate);
                $newDepartureDate = $departureDate->copy()->subDay()->format('Y-m-d'); // one day earlier

                $newInboundDate = null;
                if ($shouldAddSecondSlice) {
                    $inboundDate = \Carbon\Carbon::parse($request->departureDateInbound);
                    $newInboundDate = $inboundDate->copy()->addDay()->format('Y-m-d'); // one day later
                }

                \Log::info("No offers after filtering — trying adjusted dates: outbound {$newDepartureDate}" . ($newInboundDate ? " inbound {$newInboundDate}" : ""));

                // Build adjusted slices re-using buildSlice (it reads times from original $request)
                $adjustedSlices = [
                    $buildSlice($request->origin, $request->destination, $newDepartureDate, '')
                ];
                if ($shouldAddSecondSlice) {
                    $adjustedSlices[] = $buildSlice($request->originInbound, $request->destinationInbound, $newInboundDate, 'Inbound');
                }

                // Remove any time filters from adjusted slices
                foreach ($adjustedSlices as &$slice) {
                    if (isset($slice['departure_time'])) {
                        unset($slice['departure_time']);
                    }
                    if (isset($slice['arrival_time'])) {
                        unset($slice['arrival_time']);
                    }
                }
                unset($slice);

                $adjustedRequestBody = [
                    'data' => [
                        'slices' => $adjustedSlices,
                        'passengers' => $passengers,
                        'cabin_class' => $request->cabinClass ?? null
                    ]
                ];

                // add query param to url to preserve other params (same as before)
                $adjustedUrl = $url;
                $sep = (strpos($adjustedUrl, '?') === false) ? '?' : '&';
                $adjustedUrl .= $sep . 'adjusted_search=1';

                // Log the adjusted call
                \Log::info('Adjusted Duffel request body: ' . json_encode($adjustedRequestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                // Make the adjusted request
                $httpResponse2 = Http::withHeaders($headers)->post($adjustedUrl, $adjustedRequestBody);
                \Log::debug('Adjusted Duffel response status: ' . $httpResponse2->status());
                //\Log::debug('Adjusted Duffel response body: ' . $httpResponse2->body());

                $response2 = $httpResponse2->json();
                //$time = 0;
                // Filter offers for adjusted response using the SAME $request filters but with time checks disabled via $time = 0
                if (isset($response2['data']['offers'])) {
                    $response2['data']['offers'] = $this->handleOffers($response2['data']['offers'], $request, $time);
                }

                // Return the adjusted response (even if empty)
                return $response2;
            } catch (\Exception $retryEx) {
                \Log::error('Adjusted date search failed: ' . $retryEx->getMessage());
                // fall through to return original response below
            }
        }

        return $response;
    } catch (\InvalidArgumentException $ex) {
        return response()->json(['error' => $ex->getMessage()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    /**
     * Parse date string into DateTimeImmutable set to midnight (UTC).
     * Accepts Y-m-d, d-m-Y, Y/m/d, d/m/Y, and falls back to strtotime.
     * Returns DateTimeImmutable or false on failure.
     */
    private function parseDateToDateTime($date)
    {
        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                $c = Carbon::createFromFormat('d-m-Y', $date)->startOfDay();
            } else {
                $c = Carbon::parse($date)->startOfDay();
            }
            // return a DateTime (UTC normalized) for simple format comparison
            $dt = new \DateTime($c->format('Y-m-d'), new \DateTimeZone('UTC'));
            return $dt;
        } catch (\Exception $ex) {
            return false;
        }
    }


    /**
     * Return true if $dateA is strictly before $dateB (date-only comparison).
     * Returns false if either date can't be parsed.
     */
    private function dateIsBefore($dateA, $dateB): bool
    {
        $da = $this->parseDateToDateTime($dateA);
        $db = $this->parseDateToDateTime($dateB);
        if (!$da || !$db) {
            return false;
        }

        return $da < $db;
    }

    private function offerOutboundMatchesTimeOrBeforeTourDate($offer, $arrivalTimeTo = null, $tourDate = null)
    {
        // if neither constraint provided, allow offer
        if (empty($arrivalTimeTo) && empty($tourDate)) {
            \Log::info('offerOutboundMatches: no constraints - allow', ['offer_id' => $offer['id'] ?? null]);
            return true;
        }

        // ensure slice 0 exists with at least one segment
        if (empty($offer['slices'][0]['segments'][0])) {
            \Log::info('offerOutboundMatches: missing slice0/segment - reject', ['offer_id' => $offer['id'] ?? null]);
            return false;
        }

        $segment = $offer['slices'][0]['segments'][0];

        // get destination timezone (fall back sensibly)
        $destTz = $segment['destination']['time_zone'] ?? ($offer['slices'][0]['destination']['time_zone'] ?? 'UTC');

        // parse arriving_at into local time using destination tz
        try {
            $arrDt = new \DateTime($segment['arriving_at'], new \DateTimeZone($destTz));
        } catch (\Exception $ex) {
            \Log::info('offerOutboundMatches: invalid arriving_at - reject', [
                'offer_id' => $offer['id'] ?? null,
                'arriving_at' => $segment['arriving_at'] ?? null,
                'error' => $ex->getMessage()
            ]);
            return false;
        }

        $arrLocalTime = $arrDt->format('H:i');   // e.g. '05:10'
        $arrLocalDate = $arrDt->format('Y-m-d'); // e.g. '2026-06-21'

        // Log basic parsed values
        \Log::info('offerOutboundMatches: arrival parsed', [
            'offer_id' => $offer['id'] ?? null,
            'arriving_at_raw' => $segment['arriving_at'] ?? null,
            'dest_tz' => $destTz,
            'arrLocalDate' => $arrLocalDate,
            'arrLocalTime' => $arrLocalTime,
            'arrivalTimeTo_param' => $arrivalTimeTo,
            'tourDate_param' => $tourDate,
        ]);

        // If a tourDate is provided — apply date-first logic
        if (!empty($tourDate)) {
            $tourDtObj = $this->parseDateToDateTime($tourDate);
            if ($tourDtObj === false) {
                \Log::info('offerOutboundMatches: tourDate parse failed - fallback to time-only (if arrivalTimeTo present)', [
                    'offer_id' => $offer['id'] ?? null,
                    'tourDate' => $tourDate
                ]);
                // fall through to time-only behavior below
            } else {
                $tourYmd = $tourDtObj->format('Y-m-d');

                // 1) arrives strictly before the tour date -> accept
                if ($arrLocalDate < $tourYmd) {
                    \Log::info('offerOutboundMatches: accepted by date before tourDate', [
                        'offer_id' => $offer['id'] ?? null,
                        'arrLocalDate' => $arrLocalDate,
                        'tourYmd' => $tourYmd
                    ]);
                    return true;
                }

                // 2) arrives strictly after the tour date -> reject
                if ($arrLocalDate > $tourYmd) {
                    \Log::info('offerOutboundMatches: rejected because arrival date after tourDate', [
                        'offer_id' => $offer['id'] ?? null,
                        'arrLocalDate' => $arrLocalDate,
                        'tourYmd' => $tourYmd
                    ]);
                    return false;
                }

                // 3) same day as tourDate -> check time threshold (arrivalTimeTo must be present)
                // If arrivalTimeTo missing, we conservatively reject (you can change this)
                if (empty($arrivalTimeTo)) {
                    \Log::info('offerOutboundMatches: same-day arrival but arrivalTimeTo not provided - reject', [
                        'offer_id' => $offer['id'] ?? null,
                        'arrLocalDate' => $arrLocalDate,
                        'tourYmd' => $tourYmd
                    ]);
                    return false;
                }

                if ($this->timeCompareLessOrEqual($arrLocalTime, $arrivalTimeTo)) {
                    \Log::info('offerOutboundMatches: accepted by same-day time check', [
                        'offer_id' => $offer['id'] ?? null,
                        'arrLocalTime' => $arrLocalTime,
                        'arrivalTimeTo' => $arrivalTimeTo
                    ]);
                    return true;
                } else {
                    \Log::info('offerOutboundMatches: same-day but arrival time too late - reject', [
                        'offer_id' => $offer['id'] ?? null,
                        'arrLocalTime' => $arrLocalTime,
                        'arrivalTimeTo' => $arrivalTimeTo
                    ]);
                    return false;
                }
            }
        }

        // If we get here: either no tourDate given, or tourDate parse failed.
        // Fall back to time-only behavior if arrivalTimeTo supplied.
        if (!empty($arrivalTimeTo)) {
            if ($this->timeCompareLessOrEqual($arrLocalTime, $arrivalTimeTo)) {
                \Log::info('offerOutboundMatches: accepted by time-only check (no tourDate)', [
                    'offer_id' => $offer['id'] ?? null,
                    'arrLocalTime' => $arrLocalTime,
                    'arrivalTimeTo' => $arrivalTimeTo
                ]);
                return true;
            } else {
                \Log::info('offerOutboundMatches: rejected by time-only check (no tourDate)', [
                    'offer_id' => $offer['id'] ?? null,
                    'arrLocalTime' => $arrLocalTime,
                    'arrivalTimeTo' => $arrivalTimeTo
                ]);
                return false;
            }
        }

        // No applicable checks matched -> reject by default
        \Log::info('offerOutboundMatches: no applicable check matched - reject', ['offer_id' => $offer['id'] ?? null]);
        return false;
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
 

    private function getOffersWithoutDuffelAirways($offers)
    {
        $filteredOffers = [];
    
        foreach ($offers as $offer) {
            $skipOffer = false;
    
            foreach ($offer['slices'] as $slice) {
                if (!isset($slice['segments'])) {
                    continue;
                }
    
                foreach ($slice['segments'] as $segment) {
                    if (!isset($segment['operating_carrier']['name'])) {
                        continue;
                    }
    
                    if ($segment['operating_carrier']['name'] === 'Duffel Airways') {
                        $skipOffer = true;
                        break 2; // leave both loops
                    }
                }
            }
    
            if (!$skipOffer) {
                $filteredOffers[] = $offer;
            }
        }
    
        return $filteredOffers;
    }


    // handleOffers unchanged except it calls the simplified validateOffers
    private function handleOffers($offers, $request, $time = 0)
    {
        $offersQuantity = $request->has('limit') ? (int)$request->limit : count($offers);

        // remove Duffel Airways from all offers (no early limit)
        //$offers = $this->getOffersWithoutDuffelAirways($offers);

        // validate and stop when we have $offersQuantity
        $offers = $this->validateOffers($offers, $offersQuantity, $request, $time);

        return $offers;
    }


    /**
     * Validate offers and enforce only:
     *  - outbound: arrival_time.to  => request param: arrivalTimeTo
     *  - inbound:  departure_time.from => request param: departureTimeFromInbound
     */
    private function validateOffers($offers, $offersQuantity, $request, $time)
    {
        $validatedOffers = [];
        $count = 0;
        $timeEnabled = ((int)$time > 0);

        foreach ($offers as $offer) {
            if ($count >= $offersQuantity) {
                break;
            }

            // baggage check (keep your existing logic)
            if (!$this->validateBaggages($offer, $request)) {
                continue;
            }

        // decide whether to run outbound arrival/tourDate validation
        $hasArrivalConstraint = $timeEnabled && ($request->filled('arrivalTimeTo') && $request->filled('tourDate'));

        // LOG: record whether the constraint is considered for this request & the param values
        \Log::info('validateOffers: arrival-constraint-check', [
            'offer_id' => $offer['id'] ?? null,
            'timeEnabled' => $timeEnabled,
            'arrivalTimeTo_present' => $request->filled('arrivalTimeTo'),
            'tourDate_present' => $request->filled('tourDate'),
            'hasArrivalConstraint' => $hasArrivalConstraint,
            'arrivalTimeTo' => $request->get('arrivalTimeTo'),
            'tourDate' => $request->get('tourDate'),
        ]);

        if ($hasArrivalConstraint) {
            $arrivalTimeTo = $request->get('arrivalTimeTo'); // may be null
            $tourDate = $request->get('tourDate'); // may be null (dd-mm-YYYY or YYYY-MM-DD)

            if (!$this->offerOutboundMatchesTimeOrBeforeTourDate($offer, $arrivalTimeTo, $tourDate)) {
                \Log::info('validateOffers: offer rejected by outbound arrival/tourDate', ['offer_id' => $offer['id'] ?? null]);
                continue;
            } else {
                \Log::info('validateOffers: offer accepted by outbound arrival/tourDate', ['offer_id' => $offer['id'] ?? null]);
            }
        }



            // INBOUND: require either departure_time >= departureTimeFromInbound OR departure date > tourEndDate
            $hasInboundConstraint = $timeEnabled && ($request->filled('departureTimeFromInbound') || $request->filled('tourEndDate'));

            // LOG: whether inbound constraint will run and param values
            \Log::info('validateOffers: inbound-constraint-check', [
                'offer_id' => $offer['id'] ?? null,
                'timeEnabled' => $timeEnabled,
                'departureTimeFromInbound_present' => $request->filled('departureTimeFromInbound'),
                'tourEndDate_present' => $request->filled('tourEndDate'),
                'hasInboundConstraint' => $hasInboundConstraint,
                'departureTimeFromInbound' => $request->get('departureTimeFromInbound'),
                'tourEndDate' => $request->get('tourEndDate'),
            ]);

            if ($hasInboundConstraint) {
                $departureTimeFromInbound = $request->get('departureTimeFromInbound'); // may be null
                $tourEndDate = $request->get('tourEndDate'); // may be null (dd-mm-YYYY or YYYY-MM-DD)

                if (!$this->offerInboundMatchesTimeOrAfterTourEndDate($offer, $departureTimeFromInbound, $tourEndDate)) {
                    \Log::info('validateOffers: offer rejected by inbound time/tourEndDate', ['offer_id' => $offer['id'] ?? null]);
                    continue;
                } else {
                    \Log::info('validateOffers: offer accepted by inbound time/tourEndDate', ['offer_id' => $offer['id'] ?? null]);
                }
            }



            // other checks (stops, payment, airlines) — keep as before
            if ($request->has('stops') && !$this->validateStops($offer, $request)) {
                continue;
            }
            if ($request->has('payment') && !$this->validatePayment($offer, $request)) {
                continue;
            }
            if ($request->has('airlines') && !$this->validateAirlines($offer, $request)) {
                continue;
            }

            // passed everything
            $validatedOffers[] = $offer;
            $count++;
        }

        // sort and return
        $validatedOffers = $this->sortOffers($validatedOffers, $request);
        return $validatedOffers;
    }

    /**
     * Normalize a date param (dd-mm-YYYY or YYYY-MM-DD or parseable) to Y-m-d string.
     * Returns false on parse failure.
     */
    private function normalizeDateParamToYmd($dateRaw)
    {
        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateRaw)) {
                $dt = \Carbon\Carbon::createFromFormat('d-m-Y', $dateRaw)->startOfDay();
            } else {
                $dt = \Carbon\Carbon::parse($dateRaw)->startOfDay();
            }
            return $dt->format('Y-m-d');
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Check whether a segment departing_at (with timezone) has the same local Y-m-d as $requestedDateRaw.
     * Returns true if they match, false otherwise (or on parse error).
     */
    private function segmentDepartsOnRequestedDate($segment, $requestedDateRaw)
    {
        if (empty($segment['departing_at'])) return false;

        $originTz = $segment['origin']['time_zone'] ?? ($segment['origin']['time_zone'] ?? 'UTC'); // try segment then fallback
        try {
            $depDt = new \DateTime($segment['departing_at'], new \DateTimeZone($originTz));
        } catch (\Exception $ex) {
            return false;
        }

        $depLocalDate = $depDt->format('Y-m-d');

        $requestedYmd = $this->normalizeDateParamToYmd($requestedDateRaw);
        if ($requestedYmd === false) return false;

        return ($depLocalDate === $requestedYmd);
    }

    /**
     * Outbound arrival_time.to check (slice 0). Returns true if either:
     *  - there is no slice 0, or
     *  - arrivalTimeTo is not set, or
     *  - first segment arriving_at local time <= arrivalTimeTo
     */
    private function offerOutboundArrivesBeforeOrEqual($offer, $arrivalTimeTo)
    {
        if (empty($offer['slices'][0]['segments'][0]['arriving_at'])) {
            // malformed or missing times -> treat as non-matching
            return false;
        }

        $segment = $offer['slices'][0]['segments'][0];
        $destTz  = $segment['destination']['time_zone'] ?? ($offer['slices'][0]['destination']['time_zone'] ?? 'UTC');

        $arrDt = new \DateTime($segment['arriving_at'], new \DateTimeZone($destTz));
        $arrLocal = $arrDt->format('H:i');

        // return true if arrLocal <= arrivalTimeTo
        return $this->timeCompareLessOrEqual($arrLocal, $arrivalTimeTo);
    }

    /**
     * Inbound departure_time.from check (slice 1). Returns true if either:
     *  - there is no slice 1 (no inbound), or
     *  - departureTimeFromInbound not set, or
     *  - first segment departing_at local time >= departureTimeFromInbound
     */
    private function offerInboundDepartsAfterOrEqual($offer, $departureTimeFromInbound)
    {
        if (empty($offer['slices'][1]['segments'][0]['departing_at'])) {
            // no inbound slice or missing times -> fail
            return false;
        }

        $segment = $offer['slices'][1]['segments'][0];
        $originTz = $segment['origin']['time_zone'] ?? ($offer['slices'][1]['origin']['time_zone'] ?? 'UTC');

        $depDt = new \DateTime($segment['departing_at'], new \DateTimeZone($originTz));
        $depLocal = $depDt->format('H:i');

        // return true if depLocal >= departureTimeFromInbound
        return $this->timeCompareGreaterOrEqual($depLocal, $departureTimeFromInbound);
    }

    /**
     * Compare HH:MM strings (inclusive)
     */

    private function timeCompareLessOrEqual($timeA, $timeB)
     {
         $ta = \DateTime::createFromFormat('H:i', $timeA);
         $tb = \DateTime::createFromFormat('H:i', $timeB);
         if (!$ta || !$tb) return false;
         return $ta <= $tb;
     }
     

    private function timeCompareGreaterOrEqual($timeA, $timeB)
    {
        $ta = \DateTime::createFromFormat('H:i', $timeA);
        $tb = \DateTime::createFromFormat('H:i', $timeB);
        if (!$ta || !$tb) return false;
        return $ta >= $tb;
    }

    /**
     * Return true if the offer's inbound (slice 1) departure satisfies:
     *   - departure local time >= $departureTimeFromInbound (if provided)
     * OR
     *   - departure local date > $tourEndDate (strictly after) (if provided)
     *
     * If both args are empty, returns true (no constraint).
     * Defensive: returns false if slice/segment/fields are missing.
     */

     private function offerInboundMatchesTimeOrAfterTourEndDate($offer, $departureTimeFromInbound = null, $tourEndDate = null)
     {
         // if neither constraint provided, allow offer
         if (empty($departureTimeFromInbound) && empty($tourEndDate)) {
             \Log::info('offerInboundMatches: no constraints - allow', ['offer_id' => $offer['id'] ?? null]);
             return true;
         }
 
         // ensure slice 1 exists with at least one segment
         if (empty($offer['slices'][1]['segments'][0])) {
             \Log::info('offerInboundMatches: missing slice1/segment - reject', ['offer_id' => $offer['id'] ?? null]);
             return false;
         }
 
         $segment = $offer['slices'][1]['segments'][0];
 
         // origin timezone (departure local)
         $originTz = $segment['origin']['time_zone'] ?? ($offer['slices'][1]['origin']['time_zone'] ?? 'UTC');
 
         // parse departing_at into local time using origin tz
         try {
             $depDt = new \DateTime($segment['departing_at'], new \DateTimeZone($originTz));
         } catch (\Exception $ex) {
             \Log::info('offerInboundMatches: invalid departing_at - reject', ['offer_id' => $offer['id'] ?? null, 'departing_at' => $segment['departing_at'] ?? null, 'error' => $ex->getMessage()]);
             return false;
         }
 
         $depLocalTime = $depDt->format('H:i');   // e.g. '21:00'
         $depLocalDate = $depDt->format('Y-m-d'); // e.g. '2025-12-13'
 
         // Log parsed inbound departure
         \Log::info('offerInboundMatches: inbound parsed', [
             'offer_id' => $offer['id'] ?? null,
             'departing_at_raw' => $segment['departing_at'] ?? null,
             'origin_tz' => $originTz,
             'depLocalDate' => $depLocalDate,
             'depLocalTime' => $depLocalTime,
             'departureTimeFromInbound_param' => $departureTimeFromInbound,
             'tourEndDate_param' => $tourEndDate,
         ]);
 
         // 1) If departureTimeFromInbound provided and passes -> accept immediately
         if (!empty($departureTimeFromInbound)) {
             if ($this->timeCompareGreaterOrEqual($depLocalTime, $departureTimeFromInbound)) {
                 \Log::info('offerInboundMatches: accepted by time check', ['offer_id' => $offer['id'] ?? null, 'depLocalTime' => $depLocalTime, 'threshold' => $departureTimeFromInbound]);
                 return true;
             } else {
                 \Log::info('offerInboundMatches: failed time check', ['offer_id' => $offer['id'] ?? null, 'depLocalTime' => $depLocalTime, 'threshold' => $departureTimeFromInbound]);
             }
         }
 
         // 2) If tourEndDate provided:
         if (!empty($tourEndDate)) {
             $tourEndDtObj = $this->parseDateToDateTime($tourEndDate);
             if ($tourEndDtObj !== false) {
                 $tourEndYmd = $tourEndDtObj->format('Y-m-d');
 
                 // If departure date is strictly after tourEndDate -> accept
                 if ($depLocalDate > $tourEndYmd) {
                     \Log::info('offerInboundMatches: accepted by date after tourEnd', ['offer_id' => $offer['id'] ?? null, 'depLocalDate' => $depLocalDate, 'tourEnd' => $tourEndYmd]);
                     return true;
                 }
 
                 // If departure date equals tourEndDate, accept only if time >= departureTimeFromInbound (if present)
                 if ($depLocalDate === $tourEndYmd) {
                     if (!empty($departureTimeFromInbound)) {
                         if ($this->timeCompareGreaterOrEqual($depLocalTime, $departureTimeFromInbound)) {
                             \Log::info('offerInboundMatches: accepted by same-day + late time', ['offer_id' => $offer['id'] ?? null, 'depLocalDate' => $depLocalDate, 'depLocalTime' => $depLocalTime, 'tourEnd' => $tourEndYmd, 'threshold' => $departureTimeFromInbound]);
                             return true;
                         } else {
                             \Log::info('offerInboundMatches: same-day but time too early', ['offer_id' => $offer['id'] ?? null, 'depLocalTime' => $depLocalTime, 'threshold' => $departureTimeFromInbound]);
                         }
                     } else {
                         // conservative: if no inbound threshold provided, we're rejecting same-day returns here.
                         \Log::info('offerInboundMatches: same-day return and no departureTimeFromInbound provided - reject by default', ['offer_id' => $offer['id'] ?? null, 'depLocalDate' => $depLocalDate]);
                     }
                 }
             } else {
                 \Log::info('offerInboundMatches: tourEndDate parse failed - ignoring tourEndDate check', ['offer_id' => $offer['id'] ?? null, 'tourEndDate' => $tourEndDate]);
             }
         }
 
         // neither condition satisfied -> reject offer
         \Log::info('offerInboundMatches: rejected by both checks', [
             'offer_id' => $offer['id'] ?? null,
             'depLocalDate' => $depLocalDate,
             'depLocalTime' => $depLocalTime,
             'departureTimeFromInbound' => $departureTimeFromInbound,
             'tourEndDate' => $tourEndDate
         ]);
         return false;
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

    public function flightCancelV2(Request $r){
        try{

            $order_id = $r->order_id;

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


<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\TourIdController;
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\GustavoDuffelController;
use Illuminate\Support\Facades\Cache;
use App\Models\TourSnapshot;

class TourRadarService
{

    /**
     * Fetch and assemble featured tours for a given category code.
     * Returns up to 8 tours with merged data (cities, flights, pricing).
     */
    public function __construct()
    {
        $this->tourIdController    = new TourIdController();
        $this->tourRadarController = new TourRadarController();
        $this->tourController      = new TourController();
        $this->duffelController    = new GustavoDuffelController();
    }

    public function getFeaturedToursForCategory(string $code): array
    {
        // 1) Fetch tour IDs via our TourIdController
        $idsReq = new Request([
            'tour_type'  => $this->formatCodes($code),
            'sort_by'    => 'price_total',
            'sort_order' => 'asc',
            'limit'      => 120,
        ]);
        $idsResp = $this->tourIdController->index($idsReq);
        $idsData = json_decode($idsResp->getContent(), true)['data'] ?? [];
        $tourIds = $idsData['tour_ids'] ?? [];
    
        if (empty($tourIds)) {
            Log::warning("TourRadarService: no tour IDs for category {$code}");
            return [];
        }
    
        Log::info('Fetched tour ids', $tourIds);
    
        $itemsPerPage = 10;
        $page = 1;
        $tours = [];
    
        // 2) Loop pages until we have 8 tours or run out
        while (count($tours) < 8) {
            $start = ($page - 1) * $itemsPerPage;
            $paginatedTourIds = array_slice($tourIds, $start, $itemsPerPage);
    
            if (empty($paginatedTourIds)) {
                break;
            }
    
            $starts = Carbon::now()->addMonths(3)->startOfMonth()->format('Y-m-d');
            $ends   = Carbon::now()->addMonths(3)->endOfMonth()->format('Y-m-d');
    
            $req = new Request([
                'date_range'   => "{$starts},{$ends}",
                'page'         => 1,                       // only first page for each tour chunk
                'tourIds'      => implode(',', $paginatedTourIds),
                'travelers'    => 1,
                'user_country' => 185,
                'currency'     => 'USD',
            ]);
    
            // Call the controller method directly (it returns a JsonResponse)
            $resp = $this->tourRadarController->getMultipleDeparturesOnlyDb($req);
    
            // Decode JSON response to array
            $respData = json_decode($resp->getContent(), true);
            $departuresItems = $respData['items'] ?? [];
    
            Log::info('Responses', $respData);
    
            // Process items immediately (don't accumulate heavy arrays)
            if (!empty($departuresItems)) {
                $merged = $this->processRadarItems($departuresItems,$code);
                foreach ($merged as $tour) {
                    if (count($tours) >= 8) break;
                    $tours[] = $tour;
                }
            }
    
            // --- FREE chunk-level memory here ---
            // remove large references created this iteration
            unset($resp, $respData, $departuresItems);
    
            // if $merged can be large, unset it too (we've already consumed it)
            if (isset($merged)) {
                unset($merged);
            }
    
            // Ask PHP to collect cycles and free memory now
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
    
            // small delay if you still want it
            usleep(500000); // 0.5s (non-blocking better than sleep for CLI)
    
            $page++;
        }
    
        // Final cleanup (just in case)
        if (isset($merged)) unset($merged);
        gc_collect_cycles();
    
        return $tours;
    }

    protected function findCheapestAccommodationFromItems(array $items): ?array
    {
        $best = null;
        $bestValue = null;

        foreach ($items as $item) {
            // Normalize possible places for cheapest accommodation:
            // 1) top-level keys on item
            $candidates = [];

            $top = data_get($item, 'cheapest_accommodation') ?? data_get($item, 'cheapestAccommodation');
            if ($top) $candidates[] = $top;

            // 2) some items may have a 'departures' array with cheapestAccommodation on each departure
            if (!empty($item['departures']) && is_array($item['departures'])) {
                foreach ($item['departures'] as $dep) {
                    $depCa = data_get($dep, 'cheapest_accommodation') ?? data_get($dep, 'cheapestAccommodation');
                    if ($depCa) $candidates[] = $depCa;
                }
            }

            // Evaluate all candidates for this item
            foreach ($candidates as $cand) {
                // If candidate is JSON string, try decode it
                if (is_string($cand)) {
                    $decoded = json_decode($cand, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $cand = $decoded;
                    }
                }

                // Must be an array/object with a numeric 'value'
                if (!is_array($cand) && !is_object($cand)) {
                    continue;
                }

                // normalize to array
                $candArr = (array) $cand;

                if (!array_key_exists('value', $candArr)) {
                    continue;
                }

                // sanitize and cast value
                $raw = $candArr['value'];
                // remove commas/spaces, then cast
                if (is_string($raw)) {
                    $rawClean = str_replace([',', ' '], ['', ''], $raw);
                    $val = is_numeric($rawClean) ? (float)$rawClean : null;
                } elseif (is_numeric($raw)) {
                    $val = (float)$raw;
                } else {
                    $val = null;
                }

                // discard invalid or non-positive values
                if ($val === null || $val <= 0) {
                    continue;
                }

                // keep the cheapest
                if ($bestValue === null || $val < $bestValue) {
                    $bestValue = $val;
                    $best = $candArr; // keep full object (array form)
                }
            }
        }

        if ($best === null) {
            return null;
        }

        return ['object' => $best, 'value' => $bestValue];
    }

    protected function formatCodes(string $code): string
    {
        if ($code === 'all') {
            $all = config('services.tourradar.category_codes');
            return implode(',', array_filter($all, fn($c) => $c !== 'all'));
        }
        return $code;
    }


    protected function processRadarItems($items, $code)
    {
        if (empty($items)) {
            return [];
        }

        // Normalize and index items by tour_id
        $itemsByTour = [];
        $tourIdsArr = [];

        foreach ($items as $it) {
            // Try multiple keys to find tour id (new: 'tour_id', legacy: 'tourId', or nested)
            $tid = $it['tour_id'] ?? $it['tourId'] ?? ($it['tour']['tour_id'] ?? null);
            if (!$tid) {
                // If it's a raw departure with 'tour_id' inside child, try that
                $tid = $it['tour']['tour_id'] ?? ($it['tour_id'] ?? null);
            }
            if (!$tid) {
                continue;
            }
            $tid = (string)$tid;
            $itemsByTour[$tid][] = $it;
            $tourIdsArr[$tid] = $tid;
        }

        if (empty($tourIdsArr)) {
            return [];
        }

        // Build the request for TourController::index (expects something like "[id,id,...]" in tour_ids)
        $toursReq = new Request([
            'tour_ids'   => '[' . implode(',', array_values($tourIdsArr)) . ']',
            'sort_by'    => 'price_total',
            'sort_order' => 'asc',
            'limit'      => 120,
        ]);

        $toursResp = $this->tourController->index($toursReq);

        $details = json_decode($toursResp->getContent(), true)['data'] ?? [];

        Log::info('Tours details', $details);

        $output = [];

        foreach ($details as $tour) {
            $tid = (string)($tour['tour_id'] ?? $tour['id'] ?? null);
            if (!$tid) {
                continue;
            }

            // collect all related items for this tour
            $relatedItems = $itemsByTour[$tid] ?? [];

            // If the related items are already the structure with 'departures' arrays,
            // merge all departures; otherwise assume related items are raw departures.
            $mergedDepartures = [];

            foreach ($relatedItems as $r) {
                if (isset($r['departures']) && is_array($r['departures'])) {
                    // r already contains departures array (new structure)
                    $mergedDepartures = array_merge($mergedDepartures, $r['departures']);
                } else {
                    // Legacy: r is a departure itself
                    $mergedDepartures[] = $r;
                }
            }

            // Filter: only keep departures having cheapestAccommodation.value > 0
            $filtered = array_filter($mergedDepartures, function ($dep) {
                // support both snake and camel
                $ca = data_get($dep, 'cheapest_accommodation') ?? data_get($dep, 'cheapestAccommodation');

                if ($ca === null) {
                    return false;
                }

                // If it's a JSON string, try to decode
                if (is_string($ca)) {
                    $decoded = json_decode($ca, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $ca = $decoded;
                    } else {
                        // not decodable, reject
                        return false;
                    }
                }

                // normalize to array
                if (is_object($ca)) {
                    $ca = (array) $ca;
                }

                if (!is_array($ca) || !array_key_exists('value', $ca)) {
                    return false;
                }

                $raw = $ca['value'];

                // sanitize numeric strings like "1,234.56" or " 120 "
                if (is_string($raw)) {
                    $clean = str_replace([',', ' '], ['', ''], $raw);
                    if (!is_numeric($clean)) {
                        return false;
                    }
                    $val = (float)$clean;
                } elseif (is_numeric($raw)) {
                    $val = (float)$raw;
                } else {
                    return false;
                }

                // only accept strictly positive values
                return $val > 0;
            });

            // Reindex and attach departures to the tour detail
            $tour['departures'] = array_values($filtered);


            $startId = $tour['start_city'] ?? $tour['startCity'] ?? null;
            $endId   = $tour['end_city']   ?? $tour['endCity']   ?? null;

            $tour['startCityName'] = $startId ? $this->searchCity($startId) : null;
            $tour['endCityName']   = $endId   ? $this->searchCity($endId)   : null;

            // log with context array (no type error)
            Log::info('Resolved city names for tour', [
                'tour_id'        => $tour['tour_id'] ?? null,
                'start_city_id'  => $startId,
                'start_city'     => $tour['startCityName'],
                'end_city_id'    => $endId,
                'end_city'       => $tour['endCityName'],
            ]);

            // Determine cheapest accommodation value for the first (or cheapest) departure
            $cheapestAccValue = 0.0;
            if (!empty($tour['departures'])) { 

                // get flights
                $flight = $this->getFlightsForFirstDeparture($tour);
                usleep(1000000); // 0.5 seconds in microseconds
                if ($flight && isset($flight['price']) && (float)$flight['price'] > 0) {
                    $flightPrice = (float)$flight['price'];

                    // compute total price as before (1.15 factor)
                    $tour['totalPrice'] = 1.15 * ($flightPrice + $cheapestAccValue);

                    $tour['countriesList'] = $this->formatCountries($tour['countries'] ?? []);
                    $tour['flight'] = $flight;
                    $type = data_get($tour, 'type.0.tour_type_id') ?? null;

                    $output[] = $tour;
                    $reduced = $this->reduceToursPayload($tour);

                    //$data = json_decode($tour, true);
                    TourSnapshot::updateOrCreate(
                        ['tour_id' => $tour['tour_id']],
                        [
                            'tour_name' => $tour['tour_name'] ?? null,
                            'start_city' => $tour['start_city'] ?? null,
                            'end_city' => $tour['end_city'] ?? null,
                            'start_city_name' => $tour['startCityName'] ?? null,
                            'end_city_name' => $tour['endCityName'] ?? null,
                            'countries_list' => $tour['countriesList'] ?? null,
                            'payload' => $reduced ?? null,
                            'snapshot_at' => now(),
                            'type' => $code,
                        ]
                    );

                }
                // fallback to legacy 'cheapestAccommodation.value'
            }
            
        }
        $count = count($output);
        $sample = array_slice($output, 0, 3); // first 3 tours

        Log::info('Merged tours (sample)', [
            'count'  => $count,
            'sample' => $sample,
        ]);
        
        return $output;
    }

    protected function reduceToursPayload($tour)
    {       
            
            // helpers: data_get is available in Laravel
            $mainImage = data_get($tour, 'main_image') ?? data_get($tour, 'mainImage') ?? null;
            $tourId    = data_get($tour, 'tour_id') ?? data_get($tour, 'tourId') ?? null;
            $tourName  = data_get($tour, 'tour_name') ?? data_get($tour, 'tourName') ?? null;
            $reviews   = data_get($tour, 'reviews_count') ?? data_get($tour, 'reviewsCount') ?? null;
            $tour_length = data_get($tour, 'tour_length_days') ?? null;
            $tour_countries = data_get($tour, 'countries') ?? null;
            // totalPrice fallbacks: totalPrice (computed), total_price or price_total
            $totalPrice = data_get($tour, 'totalPrice');
            if ($totalPrice === null) {
                $totalPrice = data_get($tour, 'total_price') ?? data_get($tour, 'price_total');
            }

            // cheapest accommodation: prefer tour.cheapest_accommodation then legacy keys; if absent, scan departures
            $cheapest = data_get($tour, 'departures.0.cheapestAccommodation')
                ?? data_get($tour, 'departures.0.cheapest_accommodation')
                ?? null;

                if ($cheapest === null && !empty($tour['departures'])) {
                    foreach ($tour['departures'] as $d) {
                        $c = data_get($d, 'cheapest_accommodation') ?? data_get($d, 'cheapestAccommodation');
                        if ($c) { $cheapest = $c; break; }
                    }
                }

            // flight offer id: try common paths
            $flightOfferId = data_get($tour, 'flight.offer.id')
                        ?? data_get($tour, 'flight.offerId')
                        ?? data_get($tour, 'flight.offer.id')
                        ?? data_get($tour, 'flight.offer_id')
                        ?? data_get($tour, 'flight.id')
                        ?? null;

            // flight total amount: try flight.total_amount, flight_total_amount, flight.price, flight.price_total
            $flightTotalAmount = data_get($tour, 'flight_total_amount')
                            ?? data_get($tour, 'flight.total_amount')
                            ?? data_get($tour, 'flight.price')
                            ?? data_get($tour, 'flight.price_total');

            // Normalize types
            $reviews = $reviews !== null ? (int)$reviews : null;
            $totalPrice = $totalPrice !== null ? (float)$totalPrice : null;
            $flightTotalAmount = $flightTotalAmount !== null ? (float)$flightTotalAmount : null;
            $package_price =  ($totalPrice + $flightTotalAmount) *1.15;
            return [
                'main_image' => $mainImage,
                'tour_id' => $tourId !== null ? (int)$tourId : null,
                'tour_name' => $tourName,
                'reviews_count' => $reviews,
                'totalPrice' => $totalPrice,
                'cheapest_accommodation' => $cheapest,
                'flight_offer_id' => $flightOfferId,
                'flight_total_amount' => $flightTotalAmount,
                'tour_length' => $tour_length,
                'package_price' => $package_price,
                'countries' => $tour_countries,
            ];
    }

    protected function getFlightsForFirstDeparture(array $tour): ?array
    {
        \Log::info('getFlightsForFirstDeparture start', ['tour_id' => $tour['tour_id'] ?? null]);
        // capture both possible candidates
        $depCandidateFromDepartures = data_get($tour, 'departures.0');
        $depCandidateFromDeparture  = data_get($tour, 'departure.0');

        // log a compact, safe summary (avoid huge dumps)
        \Log::info('First-departure candidates', [
            'tour_id'         => $tour['tour_id'] ?? null,
            // prefer compact JSON for complex nested arrays
            'departures_0'    => $depCandidateFromDepartures ? json_encode($depCandidateFromDepartures, JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
            'departure_0'     => $depCandidateFromDeparture  ? json_encode($depCandidateFromDeparture, JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
        ]);

        // now pick the first available
        $dep = $depCandidateFromDepartures ?? $depCandidateFromDeparture;


        $dep = data_get($tour, 'departures.0') ?? data_get($tour, 'departure.0');
        if (! $dep) {
            Log::info('No departure found for tour', ['tour_id' => $tour['tour_id'] ?? null]);
            return null;
        }

        \Log::info('Using departure', ['tour_id' => $tour['tour_id'] ?? null, 'departure' => $dep]);

        $startDate = $dep['date'];
        $length    = $tour['tour_length_days'];
        \Log::info('Dates and length', ['startDate' => $startDate, 'length' => $length]);

        $endDate  = $this->calculateTourEndDate($startDate, $length)->format('Y-m-d');
        $fromDate = $this->formatDateForDuffel($startDate, -1);
        $toDate   = $this->formatDateForDuffel($startDate, 0);

        \Log::info('Computed date window', ['from' => $fromDate, 'to' => $toDate, 'endDate' => $endDate]);

        $originCode = $this->getDuffelIDFromTourradarID($tour['start_city'] ?? $tour['startCity'] ?? null);
        $destCode   = $this->getDuffelIDFromTourradarID($tour['end_city'] ?? $tour['endCity'] ?? null);

        \Log::info('Mapped airport codes', ['originCode' => $originCode, 'destCode' => $destCode, 'tour_id' => $tour['tour_id'] ?? null]);

        if (! $originCode || ! $destCode) {
            \Log::info('Missing origin or destination code, aborting flight lookup', ['origin' => $originCode, 'dest' => $destCode, 'tour_id' => $tour['tour_id'] ?? null]);
            return null;
        }

        $flightPayload = [
            'origin'       => 'NYC',
            'startCity'    => $originCode,
            'endCity'      => $destCode,
            'departure'    => $fromDate,
            'arrival'      => $endDate,
            'adultsCount'  => 1,
            'childrenCount'=> 0,
        ];

        \Log::info('Requesting Duffel offers', ['payload' => $flightPayload, 'tour_id' => $tour['tour_id'] ?? null]);

        try {
            // build Request to call your Duffel controller method
            $flightReq = new Request($flightPayload);
            $offerResp = $this->duffelController->offerRequests($flightReq);

            // controller might return a JsonResponse or an array — handle both
            if (is_object($offerResp) && method_exists($offerResp, 'getContent')) {
                $offerData = json_decode($offerResp->getContent(), true);
            } elseif (is_array($offerResp)) {
                $offerData = $offerResp;
            } else {
                $offerData = null;
            }

            \Log::info('Duffel response raw', [
                'offer_data_exists' => is_array($offerData),
                'tour_id' => $tour['tour_id'] ?? null,
            ]);

            $offer = $offerData['offers'][0] ?? null;
            if (! $offer) {
                \Log::info('No offers returned by Duffel', ['tour_id' => $tour['tour_id'] ?? null]);
                return null;
            }

            \Log::info('Duffel Offer selected', ['tour_id' => $tour['tour_id'] ?? null, 'offer_sample' => [
                'total_amount' => data_get($offer, 'total_amount'),
                'slices_count' => count($offer['slices'] ?? []),
            ]]);

            $price = data_get($offer, 'total_amount');
            $depart = data_get($offer, 'slices.0.segments.0.departing_at');

            // get last slice/segment safely
            $slices = $offer['slices'] ?? [];
            $lastSliceIndex = count($slices) - 1;
            $arrive = null;
            if ($lastSliceIndex >= 0 && isset($slices[$lastSliceIndex]['segments'])) {
                $segments = $slices[$lastSliceIndex]['segments'];
                $lastSegIndex = count($segments) - 1;
                $arrive = $segments[$lastSegIndex]['arriving_at'] ?? null;
            }

            \Log::info('Parsed offer info', ['price' => $price, 'depart' => $depart, 'arrive' => $arrive, 'tour_id' => $tour['tour_id'] ?? null]);

            return ['price' => $price, 'departure' => $depart, 'arrival' => $arrive, 'offer' => $offer];

        } catch (\Exception $e) {
            \Log::error('Error fetching Duffel offers', ['message' => $e->getMessage(), 'tour_id' => $tour['tour_id'] ?? null]);
            return null;
        }
    }


    protected function loadDestinations(): array
    {
        return Cache::remember('local:destinations_json', 60 * 60, function () {
            $path = public_path('destinations.json');
            if (! file_exists($path)) {
                \Log::error("destinations.json not found at {$path}");
                return [];
            }
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Invalid JSON in destinations.json: ' . json_last_error_msg());
                return [];
            }
            return $data;
        });
    }

    protected function searchCity($tId): string
    {
        $cities = $this->loadDestinations();
        $found = collect($cities)->first(fn($c) => ($c['t_id'] ?? null) == $tId);
        return $found['label'] ?? 'Unknown';
    }

    

    protected function calculateTourEndDate(string $start, int $length): Carbon
    {
        return Carbon::parse($start)->addDays($length);
    }

    protected function formatDateForDuffel(string $date, int $offsetDays): string
    {
        return Carbon::parse($date)->addDays($offsetDays)->format('Y-m-d');
    }

    protected function loadStartEndMap(): array
    {
        return Cache::remember('local:start_end_json', 60 * 60, function () {
            $path = public_path('start-end.json');
            if (! file_exists($path)) {
                \Log::error("start-end.json not found at {$path}");
                return [];
            }
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Invalid JSON in start-end.json: ' . json_last_error_msg());
                return [];
            }
            return $data;
        });
    }

    protected function getDuffelIDFromTourradarID(string $id): ?string
    {
        $map = $this->loadStartEndMap();
        $found = collect($map)->first(fn($c) => ($c['t_city'] ?? null) == $id);
        return $found['code'] ?? null;
    }

    protected function formatCountries(array $countries): string
    {
        if (empty($countries)) {
            return '';
        }
    
        return collect($countries)
            ->map(function ($item) {
                // Country name may be nested under 'country.name' or be 'name' directly
                if (is_array($item)) {
                    if (isset($item['country']['name'])) return trim($item['country']['name']);
                    if (isset($item['name'])) return trim($item['name']);
                }
    
                if (is_object($item)) {
                    if (isset($item->country) && isset($item->country->name)) return trim($item->country->name);
                    if (isset($item->name)) return trim($item->name);
                }
    
                // fallback: try data_get
                $name = data_get($item, 'country.name') ?: data_get($item, 'name');
                return $name ? trim($name) : null;
            })
            ->filter()      // drop null/empty values
            ->unique()      // remove duplicates
            ->values()      // reindex
            ->join(', ');
    }
    
}


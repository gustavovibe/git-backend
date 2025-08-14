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
            
            $departures = array_merge($departures ?? [], $departuresItems);

            Log::info('Chunk departures count', ['page' => $page, 'count' => count($departuresItems)]);

            if (!empty($departuresItems)) {
                $merged = $this->processRadarItems($departuresItems);
                foreach ($merged as $tour) {
                    if (count($tours) >= 8) break;
                    $tours[] = $tour;
                }
            }
            sleep(0.2);
            $page++;
        }

        return $tours;
    }


    protected function formatCodes(string $code): string
    {
        if ($code === 'all') {
            $all = config('services.tourradar.category_codes');
            return implode(',', array_filter($all, fn($c) => $c !== 'all'));
        }
        return $code;
    }


    protected function processRadarItems(array $items): array
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

            // Attach departures to the tour detail
            $tour['departures'] = array_values($mergedDepartures);


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
                usleep(500000); // 0.5 seconds in microseconds
                if ($flight && isset($flight['price']) && (float)$flight['price'] > 0) {
                    $flightPrice = (float)$flight['price'];

                    // compute total price as before (1.15 factor)
                    $tour['totalPrice'] = 1.15 * ($flightPrice + $cheapestAccValue);

                    $tour['countriesList'] = $this->formatCountries($tour['countries'] ?? []);
                    $tour['flight'] = $flight;

                    $output[] = $tour;
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
                            'payload' => $tour,
                            'snapshot_at' => now(),
                        ]
                    );

                }
                // fallback to legacy 'cheapestAccommodation.value'
                $firstDeparture = $tour['departures'][0];

                $cheapAcc = data_get($firstDeparture, 'cheapest_accommodation', null);
                if ($cheapAcc === null) {
                    $cheapAcc = data_get($firstDeparture, 'cheapestAccommodation', null);
                }

                if (is_array($cheapAcc) && isset($cheapAcc['value'])) {
                    $cheapestAccValue = (float)$cheapAcc['value'];
                } else {
                    // try to find any departure with a cheapest_accommodation
                    foreach ($tour['departures'] as $d) {
                        $ca = data_get($d, 'cheapest_accommodation', null) ?: data_get($d, 'cheapestAccommodation', null);
                        if (is_array($ca) && isset($ca['value'])) {
                            $cheapestAccValue = (float)$ca['value'];
                            break;
                        }
                    }
                }   
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

            return ['price' => $price, 'departure' => $depart, 'arrival' => $arrive];

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
        return collect($countries)
            ->slice(1)
            ->pluck('country.name')
            ->join(', ');
    }
}


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
use App\Http\Controllers\DuffelApiController;

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
        $this->duffelController    = new DuffelApiController();
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
        // fetch detailed tours and merge with each departure
        $tourIds = collect($items)->pluck('tourId')->unique()->implode(',');
        if (empty($tourIds)) {
            return [];
        }

        // Use our existing TourController index via resource route
        $toursReq = new Request([
            'tour_ids'   => '[' . implode(',', $tourIds) . ']',
            'sort_by'    => 'price_total',
            'sort_order' => 'asc',
            'limit'      => 120,
        ]);
        $toursResp = $this->tourController->index($toursReq);
        $details = json_decode($toursResp->getContent(), true)['data'] ?? [];

        Log::info('Tours details', $details);

        $output = [];

        foreach ($details as $tour) {
            $related = array_filter($items, fn($i) => $i['tourId'] == $tour['tour_id']);
            $tour['departure'] = array_values($related);

            $tour['startCityName'] = $this->searchCity($tour['start_city']);
            $tour['endCityName']   = $this->searchCity($tour['end_city']);

            $flight = $this->getFlightsForFirstDeparture($tour);
            if ($flight && $flight['price'] > 0) {
                $cheapestAcc = data_get($tour, 'departure.0.cheapestAccommodation.value', 0);
                $tour['totalPrice']    = (1.15 * ($flight['price'] + $cheapestAcc));
                $tour['countriesList'] = $this->formatCountries($tour['countries']);
                $tour['flight']        = $flight;
                $output[] = $tour;
            }
        }

        return $output;
    }


    protected function searchCity($tId): string
    {
        $resp = Http::acceptJson()->get("https://hopeful-nobel.74-208-189-166.plesk.page/destinations.json");
        $cities = $resp->ok() ? $resp->json() : [];
        $found = collect($cities)->first(fn($c) => $c['t_id'] == $tId);
        return $found['label'] ?? 'Unknown';
    }

    protected function getFlightsForFirstDeparture(array $tour): ?array
    {
        $dep = data_get($tour, 'departure.0');
        if (! $dep) {
            return null;
        }

        $startDate = $dep['date'];
        $length    = $tour['tour_length_days'];

        $endDate   = $this->calculateTourEndDate($startDate, $length)->format('Y-m-d');
        $fromDate  = $this->formatDateForDuffel($startDate, -1);
        $toDate    = $this->formatDateForDuffel($startDate, 0);

        $originCode = $this->getDuffelIDFromTourradarID($tour['start_city']);
        $destCode   = $this->getDuffelIDFromTourradarID($tour['end_city']);

        if (! $originCode || ! $destCode) {
            return null;
        }

        $flightReq = new Request([
                'origin'       => 'NYC',
                'startCity'    => $tour['start_city'],
                'endCity'      => $tour['end_city'],
                'departure'    => $tour['departure']['date'],
                'arrival'      => Carbon::parse($tour['departure']['date'])
                                        ->addDays($tour['tour_length_days'])
                                        ->format('Y-m-d'),
                'adultsCount'  => 1,
                'childrenCount'=> 0,
            ]);
        $offerResp = $this->duffelController->offerRequests($flightReq);
        $offer = json_decode($offerResp->getContent(), true)['offers'][0] ?? [];

        Log::info('Duffel Offer', $offer);

        $price = data_get($offer, 'total_amount');
        $depart = data_get($offer, 'slices.0.segments.0.departing_at');
        $arrive = data_get($offer, 'slices.-1.segments.-1.arriving_at');

        return ['price' => $price, 'departure' => $depart, 'arrival' => $arrive];
    }

    protected function calculateTourEndDate(string $start, int $length): Carbon
    {
        return Carbon::parse($start)->addDays($length);
    }

    protected function formatDateForDuffel(string $date, int $offsetDays): string
    {
        return Carbon::parse($date)->addDays($offsetDays)->format('Y-m-d');
    }

    protected function getDuffelIDFromTourradarID(string $id): ?string
    {
        $resp = Http::acceptJson()->get("https://hopeful-nobel.74-208-189-166.plesk.page/start-end.json");
        $map = $resp->ok() ? $resp->json() : [];
        $found = collect($map)->first(fn($c) => $c['t_city'] == $id);
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


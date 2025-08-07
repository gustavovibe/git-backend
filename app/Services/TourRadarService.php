<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\TourIdController;
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\DuffelApiController;
use Illuminate\Support\Facades\Log;

class TourRadarService
{
    /**
     * Fetch and assemble featured tours for a given category code.
     * Returns up to 8 tours with merged data (cities, flights, pricing).
     */
    public function __construct()
    {
        $this->tourIdController     = new TourIdController();
        $this->tourRadarController  = new TourRadarController();
        $this->tourController       = new TourController();
        $this->duffelController     = new DuffelApiController();
    }

    public function getFeaturedToursForCategory(string $code): array
    {
        // 1) Fetch tour IDs via our TourIdController
        $idsRequest = $this->tourIdController->index(
            app('request')->merge([
                'tour_type'  => $this->formatCodes($code),
                'sort_by'    => 'price_total',
                'sort_order' => 'asc',
                'limit'      => 120,
            ])
        );
        $idsResponse = App::handle($idsRequest);
        $idsData = json_decode($idsResponse->getContent(), true)['data'] ?? [];
        $tourIds = $idsData['tour_ids'] ?? [];

        if (empty($tourIds)) {
            Log::warning("TourRadarService: no tour IDs for category {$code}");
            return [];
        }

        Log::info('Fetched tour ids', $tourIds);

        $tours = [];
        $page = 1;

        // 2) Loop pages until we have 8 tours or run out
        while (count($tours) < 8) {
            $filterRequest = $this->tourRadarController->getMultipleDeparturesByTours(
                app('request')->merge([
                'tourIds'   => implode(',', $tourIds),
                'page'      => $page,
            ] + $this->defaultRangeParams())
            );

            $filterResponse = App::handle($filterRequest);
            Log::info('Filtered departures', $filterResponse);

            $items = json_decode($filterResponse->getContent(), true)['items'] ?? [];

            if (empty($items)) {
                break;
            }

            $merged = $this->processRadarItems($items);
            foreach ($merged as $tour) {
                if (count($tours) >= 8) break;
                $tours[] = $tour;
            }

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

    protected function defaultRangeParams(): array
    {
        $start = Carbon::now()->addMonths(2)->startOfMonth()->format('Y-m-d');
        $end   = Carbon::now()->addMonths(2)->endOfMonth()->format('Y-m-d');
        return ['date_range' => "[{$start},{$end}]"];
    }

    protected function processRadarItems(array $items): array
    {
        // fetch detailed tours and merge with each departure
        $tourIds = collect($items)->pluck('tourId')->unique()->implode(',');
        if (empty($tourIds)) {
            return [];
        }

        // Use our existing TourController index via resource route
        $toursRequest = $this->tourController->index(
                app('request')->merge([
                'tour_ids'   => "[{$tourIds}]",
                'sort_by'    => 'price_total',
                'sort_order' => 'asc',
                'limit'      => 120,
            ])
         );
        $toursResponse = App::handle($toursRequest);

        Log::info('Tours details', $toursResponse);

        $details = json_decode($toursResponse->getContent(), true)['data'] ?? [];

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

        $offer = $this->duffelController->createRequestGetOffers(
            app('request')->merge(
            [
                'origin'       => 'NYC',
                'startCity'    => $originCode,
                'endCity'      => $destCode,
                'departure'    => $fromDate,
                'arrival'      => $endDate,
                'adultsCount'  => 1,
                'childrenCount'=> 0,
            ]
        );

        if (! $response->ok() || empty($response->json('offers.0'))) {
            return null;
        }

        $offer = $response->json('offers.0');
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


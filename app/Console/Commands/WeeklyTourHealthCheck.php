<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Tour;
use App\Models\Departure;        // your Eloquent model for departures
use App\Models\TourCountry;      // pivot model linking tours↔countries
use App\Http\Controllers\ProxyTourRadarController;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeeklyTourHealthCheck extends Command
{
    protected $signature = 'sync:weekly-tour-health';
    protected $description = 'Weekly pick random tours per country, fetch one departure each, validate and flag is_active.';

    public function handle()
    {
        // 1) Find all country IDs that we have tours for
        $countryIds = TourCountry::distinct('t_country_id')
                      ->pluck('t_country_id');

        foreach ($countryIds as $countryId) {
            sleep(0.2);
            $this->info("Country {$countryId}: picking up to 20 tours…");

            // 2) Grab 20 random tours in that country
            $tours = Tour::whereHas('countries', fn($q) => 
                        $q->where('t_country_id', $countryId))
                      ->inRandomOrder()
                      ->limit(20)
                      ->get();

            foreach ($tours as $tour) {
                $this->line(" → Tour {$tour->tour_id}: fetching departures…");

                // 3) Fetch departures via your existing helper
                //    (reuses your private method from SyncToursData)
                // this gives “20250527-20251127” if today is May 27, 2025
            $dateRange = Carbon::now()->format('Ymd') . '-' . Carbon::now()->addMonths(6)->format('Ymd');

            $request = new Request([
                'tourId' => $tour->tour_id,
                'date_range' => $dateRange,
            ]);
            $controller = app(ProxyTourRadarController::class);
            $response = $controller->departures($request);

            $payload = $response->getData(true);

            if (empty($payload['success']) || $payload['success'] !== true) {
                $this->warn("    API error: " . json_encode($payload['error'] ?? 'unknown'));
                $tour->is_active = 3;
                $tour->save();
                continue;
            }

            $allDeps = $payload['data']['items'] ?? [];

            if (empty($allDeps)) {
                $this->warn("    No departures found.");
                $tour->is_active = 3;
                $tour->save();
                continue;
            }
            
                // 4) Pick a single departure at random
                $dep = Arr::random($allDeps);

                // 5) Validate your four rules
                $ok =
                    ($dep['availability'] > 0)
                    && ($dep['departure_type'] === 'guaranteed')
                    && ($dep['is_instant_confirmable'] === true)
                    && collect($dep['accommodations'])
                        ->pluck('beds_number')
                        ->filter(fn($beds) => $beds > 0)
                        ->isNotEmpty();

                // 6) Update tour flag
                $tour->is_active = $ok ? 2 : 3;
                $tour->save();

                $this->info("    Departure {$dep['id']} → ". ($ok ? 'PASS' : 'FAIL'));

                // 7) (Optional) Persist the sampled departure
                Departure::updateOrCreate(
                    ['tour_id' => $tour->tour_id, 'departure_id' => $dep['id']],
                    [
                        'date'        => $dep['date'],
                        'availability'=> $dep['availability'],
                        'type'        => $dep['departure_type'],
                        'instant'     => $dep['is_instant_confirmable'],
                        'raw'         => json_encode($dep),
                    ]
                );
            }
        }

        $this->info("Summary: {$totalChecked} tours checked, {$passed} passed, {$failed} failed.");
    }
}
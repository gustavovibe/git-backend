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
        // Initialize counters
        $totalChecked = 0;
        $passed       = 0;
        $failed       = 0;

        // Find all country IDs that we have tours for
        $countryIds = TourCountry::distinct('t_country_id')
                      ->pluck('t_country_id');

        foreach ($countryIds as $countryId) {
            usleep(500000);
            $this->info("Country {$countryId}: picking up to 20 tours…");

            // Grab 20 random tours in that country
            $tours = Tour::whereHas('countries', fn($q) => 
                        $q->where('t_country_id', $countryId))
                      ->inRandomOrder()
                      ->limit(10)
                      ->get();

            foreach ($tours as $tour) {
                        $totalChecked++;
                        $this->line(" → Tour {$tour->tour_id}: fetching summary departures…");
        
                        // A) Build date range string
                        $dateRange = Carbon::now()->format('Ymd')
                                   . '-' 
                                   . Carbon::now()->addMonths(6)->format('Ymd');
        
                        // B) Call the "departures" endpoint to get summaries
                        $summaryReq = new Request([
                            'tourId'     => $tour->tour_id,
                            'date_range' => $dateRange,
                        ]);
                        $summaryResp = app(ProxyTourRadarController::class)->departures($summaryReq);

                        $payload     = $summaryResp->getData(true);
        
                        // C) Validate the summary response
                        if (empty($payload['success'] ?? false)) {
                            $this->warn("    ERROR fetching departures summary.");
                            $tour->is_active = 3;
                            $tour->save();
                            continue;
                        }
        
                        $items = $payload['data']['items'] ?? [];
                        if (empty($items)) {
                            $this->warn("    No departures found.");
                            $tour->is_active = 3;
                            $tour->save();
                            continue;
                        }
        
                        // D) Pick one summary departure at random
                        $depSummary = Arr::random($items);
        
                        $this->line("    → picked departure {$depSummary['id']} (summary)");
        
                        // E) Fetch the detailed departure (to get accommodations)
                        $detailReq  = new Request([
                            'tourId'      => $tour->tour_id,
                            'departureId' => $depSummary['id'],
                        ]);

                        // 1) Call and decode (returns an array with success, data.items, etc.)
                        $detail = app(ProxyTourRadarController::class)->departure($detailReq);

                        $this->line("→ detail response: " . json_encode($detail));

                        // 2) Check for success
                        if (empty($detail['id'] ?? false)) {
                        $this->warn("ERROR fetching detailed departure.");
                        $tour->is_active = 3;
                        $tour->save();
                        continue;
                        }

                        // 3) Pull out the first item
                        $firstItem = $detail ?? null;
                        if (! $firstItem) {
                        $this->warn("No detailed prices returned.");
                        $tour->is_active = 3;
                        $tour->save();
                        continue;
                        }

                        // 4) Get the accommodations array from that first item
                        $accoms = $firstItem['prices']['accommodations'] ?? [];

                        // 5) Check that every beds_number > 0
                        $allBedsPositive = collect($accoms)
                        ->pluck('beds_number')
                        ->every(fn($n) => $n > 0);

                        // … then combine with your other summary‐level rules …
                        $ok = 
                            ($firstItem['availability'] > 0)
                            && ($firstItem['departure_type'] === 'guaranteed')
                            && ($firstItem['is_instant_confirmable'] === true)
                            && $allBedsPositive;

                        // J) Update is_active using ->update([...]) instead of ->save()
                        Tour::where('tour_id', $tour->tour_id)
                            ->update(['is_active' => $ok ? 2 : 3]);

                        if ($ok) {
                            $passed++;
                            $this->info("    → Departure {$firstItem['id']} → PASS");
                        } else {
                            $failed++;
                            $this->warn("    → Departure {$firstItem['id']} → FAIL");
                        }

                        /* J) (Optional) Persist the detailed departure
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
                        */
                    }
        }      

        $this->info("Summary: {$totalChecked} tours checked, {$passed} passed, {$failed} failed.");
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tour;
use App\Models\Departure;
use App\Http\Controllers\TourRadarController;
use Illuminate\Support\Facades\Log;

class ImportDepartures extends Command
{
    protected $signature = 'departures:import';
    protected $description = 'Import departures for all tours using the TourRadarController static method';

    public function handle()
    {
        Log::info("Starting ImportDepartures command.");
        $this->info("Starting ImportDepartures command.");

        // Retrieve all tours.
        $tours = Tour::all();
        Log::info("Retrieved tours count: " . $tours->count());
        $this->info("Retrieved tours count: " . $tours->count());

        foreach ($tours as $tour) {
            $tourId = $tour->tour_id;
            $start = Carbon::now()->addDays(1)->format('Y-m-d');
            $end   = Carbon::now()->addDays(91)->format('Y-m-d');
            $dateRange = "{$start}-{$end}";
            $params = [
                'tourId'     => $tourId,
                'date_range' => $dateRange,
            ];

            Log::info("Processing tour with ID: {$tourId} and date_range: {$dateRange}");
            $this->info("Processing tour with ID: {$tourId}");

            // Directly call the static method.
            $response = TourRadarController::getDeparturesByTour($params);
            Log::info("Received response for tour ID {$tourId}: " . json_encode($response));

            // Check for error response.
            if (isset($response['error'])) {
                Log::error("Error for tour ID {$tourId}: " . $response['error']);
                $this->error("Error for tour ID {$tourId}: " . $response['error']);
                continue;
            }

            if (isset($response['items']) && is_array($response['items'])) {
                $itemCount = count($response['items']);
                Log::info("Found {$itemCount} departure items for tour ID {$tourId}");
                $this->info("Found {$itemCount} departure items for tour ID {$tourId}");

                foreach ($response['items'] as $departureData) {
                    Log::info("Processing departure ID: " . $departureData['id'] . " for tour ID: {$tourId}");
                    $this->info("Processing departure ID: " . $departureData['id']);

                    // Map and store the departure data.
                    $departure = Departure::updateOrCreate(
                        ['id' => $departureData['id']], // Unique identifier.
                        [
                            'tour_id'               => $tourId, // save the tour_id
                            'date'                   => $departureData['date'],
                            'availability'           => $departureData['availability'],
                            'departure_type'         => $departureData['departure_type'],
                            'is_instant_confirmable' => $departureData['is_instant_confirmable'],
                            'currency'               => $departureData['currency'] ?? 'USD',
                            'based_on'               => $departureData['prices']['based_on'] ?? null,
                            'price_base'             => $departureData['prices']['price_base'] ?? 0,
                            'price_addons'           => $departureData['prices']['price_addons'] ?? 0,
                            'price_promotion'        => $departureData['prices']['price_total'] ?? 0,
                            'price_total_upfront'    => $departureData['prices']['price_total_upfront'] ?? 0,
                            'price_total'            => $departureData['prices']['price_total'] ?? 0,
                            'promotion'              => json_encode($departureData['prices']['promotion'] ?? []),
                            'mandatory_addons'       => json_encode($departureData['prices']['mandatory_addons'] ?? []),
                            'optional_extras'        => json_encode($departureData['optional_extras'] ?? []),
                        ]
                    );
                    Log::info("Saved departure ID: " . $departureData['id'] . " for tour ID: {$tourId}");
                    $this->info("Saved departure ID: " . $departureData['id']);
                }
                Log::info("Finished processing tour ID {$tourId}");
                $this->info("Finished processing tour ID {$tourId}");
            } else {
                Log::warning("No departure items found for tour ID: {$tourId}");
                $this->warn("No departure items found for tour ID: {$tourId}");
            }
        }

        Log::info("All departures imported successfully.");
        $this->info("All departures imported successfully.");
    }
}

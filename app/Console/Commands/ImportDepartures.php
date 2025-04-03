<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tour;
use App\Models\Departure;
use App\Http\Controllers\ProxyTourRadarController;
use Illuminate\Http\Request;

class ImportDepartures extends Command
{
    // The name and signature of the console command.
    protected $signature = 'departures:import';

    // The console command description.
    protected $description = 'Import departures for all tours using the internal controller method';

    public function handle()
    {
        // Instantiate the controller once.
        $controller = new ProxyTourRadarController();

        // Retrieve all tours.
        $tours = Tour::all();

        foreach ($tours as $tour) {
            $dateRange = $tour->date_range ?? '20250401-20251231';
            $params = [
                'tourId'     => $tour->tour_id,
                'date_range' => $dateRange,
                // include additional parameters if needed, e.g. 'currency'
            ];

            // Create a Request instance with our parameters.
            $request = Request::create('/departures', 'GET', $params);

            $this->info("Importing departures for tour ID: {$tour->tour_id}");

            // Call the departures method directly.
            $response = $controller->departures($request);
            
            $this->info("Response for tour ID: {$tour->tour_id}", $response);

            // Assuming your controller returns a JSON response, decode it.
            $data = $response;

            if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                foreach ($data['data']['items'] as $departureData) {
                    // Map and store the departure data.
                    Departure::updateOrCreate(
                        ['id' => $departureData['id']], // Unique key
                        [
                            'date'                    => $departureData['date'],
                            'availability'            => $departureData['availability'],
                            'departure_type'          => $departureData['departure_type'],
                            'is_instant_confirmable'  => $departureData['is_instant_confirmable'],
                            // You can set currency dynamically if the response includes it.
                            'currency'                => $departureData['currency'] ?? 'USD',
                            'based_on'                => $departureData['prices']['based_on'] ?? null,
                            'price_base'              => $departureData['prices']['price_base'] ?? 0,
                            'price_addons'            => $departureData['prices']['price_addons'] ?? 0,
                            'price_promotion'         => $departureData['prices']['price_total'] ?? 0,
                            'price_total_upfront'     => $departureData['prices']['price_total_upfront'] ?? 0,
                            'price_total'             => $departureData['prices']['price_total'] ?? 0,
                            'promotion'               => $departureData['prices']['promotion'] ?? null,
                            'mandatory_addons'        => json_encode($departureData['prices']['mandatory_addons'] ?? []),
                            // Map optional_extras if provided in your response.
                            'optional_extras'         => json_encode($departureData['optional_extras'] ?? []),
                        ]
                    );
                }
                $this->info("Departures imported for tour ID: {$tour->tour_id}");
            } else {
                $this->error("No departure items found for tour ID: {$tour->tour_id}");
            }
        }

        $this->info('All departures imported successfully.');
    }
}

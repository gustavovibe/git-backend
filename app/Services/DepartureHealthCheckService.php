<?php
// app/Services/DepartureHealthCheckService.php

namespace App\Services;

use App\Models\Tour;
use App\Models\Departure;
use App\Http\Controllers\ProxyTourRadarController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class DepartureHealthCheckService
{
    public function checkTour(Tour $tour): array
    {
        $dateRange = Carbon::now()->format('Ymd') . '-' . Carbon::now()->addMonths(6)->format('Ymd');

        $request = new Request([
            'tourId' => $tour->tour_id,
            'date_range' => $dateRange,
        ]);

        $controller = app(ProxyTourRadarController::class);
        $response = $controller->departures($request);

        // Handle invalid response
        if (!isset($response['success']) || !$response['success']) {
            return [
                'status' => 'error',
                'reason' => $response['error'] ?? 'Unknown error',
            ];
        }

        $departures = $response['data']['items'] ?? [];

        if (empty($departures)) {
            $tour->is_active = 3;
            $tour->save();
            return ['status' => 'fail', 'reason' => 'No departures'];
        }

        // Pick a random departure
        $dep = Arr::random($departures);

        $isValid = 
            ($dep['availability'] > 0)
            && ($dep['departure_type'] === 'guaranteed')
            && ($dep['is_instant_confirmable'] === true)
            && collect($dep['accommodations'])
                ->pluck('beds_number')
                ->filter(fn($beds) => $beds > 0)
                ->isNotEmpty();

        $tour->is_active = $isValid ? 2 : 3;
        $tour->save();

        return [
            'status' => $isValid ? 'pass' : 'fail',
            'departure_id' => $dep['id'],
        ];
    }
    protected function persistDeparture(array $dep, $tour): void
    {
        Departure::updateOrCreate(
            [
                'id' => $dep['id'],
                'tour_id' => $tour->tour_id,
            ],
            [
                'date' => $dep['date'],
                'availability' => $dep['availability'],
                'departure_type' => $dep['departure_type'],
                'is_instant_confirmable' => $dep['is_instant_confirmable'],
                'currency' => $dep['currency'] ?? null,
                'based_on' => $dep['based_on'] ?? null,
                'price_base' => $dep['price_base'] ?? null,
                'price_addons' => $dep['price_addons'] ?? null,
                'price_promotion' => $dep['price_promotion'] ?? null,
                'price_total_upfront' => $dep['price_total_upfront'] ?? null,
                'price_total' => $dep['price_total'] ?? null,
                'promotion' => $dep['promotion'] ?? null,
                'mandatory_addons' => $dep['mandatory_addons'] ?? null,
                'optional_extras' => $dep['optional_extras'] ?? null,
                'raw' => json_encode($dep),
            ]
        );
    }
}

<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\newPackageController;

public function processPendingAttempts()
{
    $pendingAttempts = DB::table('attempts')
        ->where('status', 'pending')
        ->where('expiration', '>=', now())
        ->get();

    foreach ($pendingAttempts as $attempt) {
        $ResponseTour = json_decode($attempt->tourradar_res, true);
        $tBookingId = $ResponseTour['id'];
        Log::info('Processing booking ID: ' . $tBookingId);

        $statusResponse = TourRadarController::checkBooking($tBookingId);
        Log::info('Status response for booking ID ' . $tBookingId . ': ' . json_encode($statusResponse));

        if (isset($statusResponse['status']) && $statusResponse['status'] == "confirmed") {
            // Retrieve flight data from the current attempt
            $flight = json_decode($attempt->flight, true);

            if (isset($flight['data']['payments'], $flight['data']['passengers'])) {
                $payments = $flight['data']['payments'][0] ?? null;
                $orderId = $tBookingId; // Using TourRadar's ID as order_id
                $flightBody = [
                    'data' => [
                        'order_id' => $orderId,
                        'payment' => [
                            'type' => $payments['type'] ?? 'balance',
                            'amount' => $payments['amount'] ?? '0.00',
                            'currency' => $payments['currency'] ?? 'USD',
                        ],
                    ],
                ];

                // Call the confirmFlight function
                $flightResponse = newPackageController::confirmFlight($flightBody);

                Log::info('Duffel response for booking ID ' . $tBookingId . ': ' . json_encode($flightResponse));

                if (isset($flightResponse['errors']) && $flightResponse['errors']) {
                    Log::error('Duffel booking failed for booking ID ' . $tBookingId);
                } else {
                    Log::info('Duffel booking successful for booking ID ' . $tBookingId);
                }
            } else {
                Log::warning('Flight data is incomplete for booking ID ' . $tBookingId);
            }

            // Update the attempt status to confirmed
            DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
            Log::info('Booking ID ' . $tBookingId . ' confirmed.');
        }
    }
}

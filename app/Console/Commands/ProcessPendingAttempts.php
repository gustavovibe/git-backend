<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\newPackageController;
use App\Http\Controllers\DuffelApiController;
use App\Http\Controllers\StripeController;

class ProcessPendingAttempts extends Command
{
    protected $signature = 'process:pending-attempts';
    protected $description = 'Process pending attempts and confirm bookings.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->processPendingAttempts();
    }

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

            try {
                $statusResponse = TourRadarController::checkBooking($tBookingId);
            } catch (\Exception $e) {
                Log::error('Error checking booking ID ' . $tBookingId . ': ' . $e->getMessage());
                continue;
            }            

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
                                'type' => $payments['type'],
                                'amount' => $payments['amount'],
                                'currency' => $payments['currency'] ?? 'USD',
                            ],
                        ],
                    ];

                    // Call the confirmFlight function
                    $flightResponse = DuffelApiController::payBooking($flightBody);
                    Log::info("Duffel response for booking ID {$tBookingId}: " . json_encode($flightResponse));

                    if (isset($flightResponse['errors']) && $flightResponse['errors']) {
                        Log::error('Duffel booking failed for booking ID ' . $tBookingId);
                    } else {
                        $paymentIntent = $attempt->payment_id;
                        Log::info('Duffel booking successful for booking ID ' . $tBookingId);
                        $stripeResponse = StripeController::capturePayment($paymentIntent);
                        Log::info('Stripe payment for payment ID ' . $paymentIntent . ': ' . json_encode($stripeResponse));
                    }
                } else {
                    Log::warning('Flight data is incomplete for booking ID ' . $tBookingId);
                }

                // Update the attempt status to confirmed
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
                Log::info('Booking ID ' . $tBookingId . ' confirmed.');
            }
        }

        $expiredAttempts = DB::table('attempts')
            ->where('status', 'pending')
            ->where('expiration', '<=', now())
            ->get();
        foreach ($expiredAttempts as $attempt) {
            $paymentIntent = $attempt->payment_id;
            $stripeResponse = StripeController::cancellPayment($paymentIntent);
            Log::info('Stripe cancell payment for payment ID ' . $paymentIntent . ': ' . json_encode($stripeResponse));
            DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
            Log::info('attempt failed (expired): ' . $attempt->id );
        }    
    }
}
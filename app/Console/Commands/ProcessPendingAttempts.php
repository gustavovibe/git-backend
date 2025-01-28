<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\newPackageController;
use App\Http\Controllers\DuffelApiController;
use App\Http\Controllers\StripeController;
use App\Models\Order;

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
            ->where('expiration', '<=', now())
            ->get();

        foreach ($pendingAttempts as $attempt) {
            Log::info('automatic Processing attempt ID: ' . $attempt->id);
            $ResponseTour = json_decode($attempt->tourradar_res, true);
            Log::info('automatic Processing $ResponseTour: ' . json_encode($ResponseTour));
            $tBookingId = $ResponseTour ? $ResponseTour['id'] : null;
            if(!$tBookingId){
                Log::error('No tBookingId found in response');
                continue;
            }
            Log::info('automatic Processing booking ID: ' . $tBookingId);

            try {
                // Check if the order exists in the database
                $order = Order::where('tourradar_id', $tBookingId)->first();
            
                if ($order) {
                    // If the order is found, return the `tourradar_status`
                    $statusResponse = $order->tourradar_status;
                    Log::info("Automatic Order found in the database. TourRadar Status: " . $statusResponse);
                    return $statusResponse; // Return the status or handle it as needed
                } else {
                    // If the order is not found, make the API call
                    $statusResponse = TourRadarController::checkBooking($tBookingId);
                    Log::info("Automatic API call made for booking ID: " . $tBookingId);
            
                }
                return $statusResponse; // Return the API response as needed
            } catch (\Exception $e) {
                Log::error('Automatic error checking booking ID ' . $tBookingId . ': ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500); // Return an error response
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
                    Log::info("automatic Duffel response for booking ID {$tBookingId}: " . json_encode($flightResponse));

                    if (isset($flightResponse['errors']) && $flightResponse['errors']) {
                        Log::error('automatic Duffel booking failed for booking ID ' . $tBookingId);
                    } else {
                        $paymentIntent = $attempt->payment_id;
                        Log::info('automatic Duffel booking successful for booking ID ' . $tBookingId);
                        $stripeResponse = StripeController::capturePayment($paymentIntent);
                        Log::info('automatic Stripe payment for payment ID ' . $paymentIntent . ': ' . json_encode($stripeResponse));
                        // Execute get paymentIntent
                        $stripePiResponse = StripeController::getPaymentIntent($paymentId);

                        // Extract the data from the JsonResponse
                        $stripePi = $stripePiResponse->getData(true); // Convert the JSON response to an associative array

                        // Check if the response has 'balance_transaction' details
                        $stripeFee = $stripePi['data']['balance_transaction']['fee'] ?? null;

                        // Log the Stripe fee (before returning any response)
                        \Log::info('Stripe Fee: ' . ($stripeFee ?? 'Not Found'));

                        if ($stripeFee !== null) {
                            // Process the fee if it exists
                            return response()->json([
                                'message' => 'Stripe fee retrieved successfully.',
                                'stripe_fee' => $stripeFee,
                            ]);
                            DB::table('orders')->where('booking_id', $attempt->booking_id)->update(['stripe_fee' => $stripeFee]);
                        } else {
                            // Handle cases where the fee is not available
                            return response()->json([
                                'message' => 'Stripe fee not found in the response.',
                            ], 404);
                        }
                    }
                } else {
                    Log::warning('automatic Flight data is incomplete for booking ID ' . $tBookingId);
                }

                // Update the attempt status to confirmed
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
                Log::info('automatic Booking ID ' . $tBookingId . ' confirmed.');
            }
        }

        $expiredAttempts = DB::table('attempts')
            ->where('status', 'pending')
            ->where('expiration', '>=', now())
            ->get();
        /*    
        foreach ($expiredAttempts as $attempt) {
            $paymentIntent = $attempt->payment_id;
            if(!$paymentIntent){
                Log::error('No payment intent found in attempt');
                continue;
            }
            try {
                //$stripeResponse = StripeController::cancellPayment($paymentIntent);
                Log::info('automatic Stripe cancell payment for payment ID ' . $paymentIntent . ': ' . json_encode($stripeResponse));
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
                Log::info('automatic attempt failed (expired): ' . $attempt->id );
            } catch (\Exception $e) {
                Log::error('automatic Error cancelling payment ID ' . $paymentIntent . ': ' . $e->getMessage());
            }
        } 
        */   
        foreach ($expiredAttempts as $attempt) {
            DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
            $cs = $attempt->checkout_session;
            if(!$cs){
                Log::error('No payment intent found in attempt: ' . $attempt->id );
                continue;
            }
            try {
                $stripeResponse = StripeController::expireSession($cs);
                Log::info('automatic Stripe cancell payment for payment ID ' . $cs . ': ' . json_encode($stripeResponse));
                Log::info('automatic attempt failed (expired): ' . $attempt->id );
            } catch (\Exception $e) {
                Log::error('automatic Error cancelling payment ID ' . $cs . ': ' . $e->getMessage());
            }
        }  
    }
}
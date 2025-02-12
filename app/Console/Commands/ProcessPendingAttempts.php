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
            ->where('expiration', '>=', now())
            ->get();

        foreach ($pendingAttempts as $attempt) {

            Log::info('automatic Processing attempt ID: ' . $attempt->id. 'expiration: ' . $attempt->expiration );
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
            } catch (\Exception $e) {
                Log::error('Database error checking order for tourradar booking ID ' . $tBookingId . ': ' . $e->getMessage());
                return; // Stop execution if a database error occurs
            }
            
            if ($order) {
                $statusResponse = strval($order->tourradar_status);
                Log::info("Automatic Order found in the database. TourRadar Status: " . $statusResponse);
            } else {
                try {
                    // If the order is not found, make the API call
                    $statusResponse = TourRadarController::checkBooking($tBookingId);
                    Log::info("Automatic API call made for tourradar booking ID: " . $tBookingId . " - Response: " . $statusResponse);
                } catch (\Exception $e) {
                    Log::error('API error checking tourradar booking ID ' . $tBookingId . ': ' . $e->getMessage());
                    return; // Stop execution if API fails
                }
            }
            Log::info("Final statusResponse before checking condition: '" . $statusResponse . "'");

            // Ensure the status check is executed correctly
            if (trim(strtolower($statusResponse)) === "confirmed") {
                // Retrieve flight data from the current attempt
                $flight = json_decode($attempt->duffel_res, true);
                Log::info('automatic Flight Data: ' . json_encode($flight));

                if (isset($flight['data']['payment_status']['awaiting_payment'])) {
                    $orderId = $attempt->order_id; // Using TourRadar's ID as order_id
                    $flightBody = [
                        'data' => [
                            'order_id' => $orderId,
                            'payment' => [
                                'type' => 'balance',
                                'amount' => $flight['data']['total_amount'],
                                'currency' => 'USD',
                            ],
                        ],
                    ];

                    // Call the confirmFlight function
                    $flightResponse = DuffelApiController::payBooking($flightBody);
                    Log::info("automatic Duffel response for booking ID {$orderId}: " . json_encode($flightResponse));

                    if (isset($flightResponse['errors']) && $flightResponse['errors']) {
                        Log::error('automatic Duffel booking failed for duffel order ID ' . $orderId);
                    } else {
                        $paymentIntent = $attempt->payment_id;
                        Log::info('automatic Duffel booking successful for duffel order ID ' . $orderId);
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
                    Log::warning('automatic Flight data is incomplete for duffel order ID ' . $orderId);
                }

                // Update the attempt status to confirmed
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
                Log::info('automatic duffel order ID ' . $orderId . ' confirmed.');
            }
        }

        $expiredAttempts = DB::table('attempts')
            ->where('status', 'pending')
            ->where('expiration', '<', now())
            ->get();
  
        foreach ($expiredAttempts as $attempt) {
            Log::info('automatic Processing expired attempt ID: ' . $attempt->id . 'expiration: ' . $attempt->expiration);
            $paymentIntent = $attempt->payment_id;
            if(!$paymentIntent){
                Log::error('No payment intent found in attempt');
                continue;
            }
            try {
                $stripePayment = StripeController::getPaymentIntent($paymentIntent);
                Log::info('automatic Stripe payment retrieved for payment ID ' . $paymentIntent . ': ' . json_encode($stripePayment));
                if ($stripePayment['data']['canceled_at'] == null) {
                    $stripeResponse = StripeController::cancellPayment($paymentIntent);
                    Log::info('automatic Stripe cancell payment for payment ID ' . $paymentIntent . ': ' . json_encode($stripeResponse));
                    DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
                    Log::info('automatic attempt failed (expired): ' . $attempt->id );
                } else {
                    DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
                    Log::info('automatic attempt already cancelled in stripe: ' . $attempt->id );
                }
            } catch (\Exception $e) {
                Log::error('automatic Error cancelling payment ID ' . $paymentIntent . ': ' . $e->getMessage());
            }
        }  
        /* 
        foreach ($expiredAttempts as $attempt) {
            Log::info('automatic Processing expired attempt ID: ' . $attempt->id . 'expiration: ' . $attempt->expiration);
            $cs = $attempt->checkout_session;
            $paymentIntent = $attempt->payment_id;
            if(!$cs){
                Log::error('No payment intent found in attempt: ' . $attempt->id );
                continue;
            }
            try {
                $stripeResponse = StripeController::expireSession($cs);
                Log::info('automatic Stripe cancell payment for payment checkout ' . $cs . ' and payment id: ' .$paymentIntent. ' response: ' . json_encode($stripeResponse));
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'failed']);
                Log::info('automatic attempt failed (expired): ' . $attempt->id );
            } catch (\Exception $e) {
                Log::error('automatic Error cancelling payment ID ' . $cs . ': ' . $e->getMessage());
                
            }
        }  
        */   
    }
}
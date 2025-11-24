<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Stripe\StripeClient;
use App\Models\Attempt;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Charge;

class StripeController extends Controller
{

    /**
     * Get payment intent.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function getPaymentIntentFromQuery(Request $request)
    {
        // Extract the `q` parameter from the request
        $paymentIntentId = $request->query('q');

        // Validate that the payment intent ID exists
        if (!$paymentIntentId) {
            return ApiResponse::error('Payment intent ID (q) is required', 400);
        }

        // Call the static method with the payment intent ID
        return self::getPaymentIntent($paymentIntentId);
    }

    public static function getPaymentIntent(string $paymentIntentId)
    {
        try {
            // Your existing code for retrieving the payment intent
            $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';
            $stripe = new \Stripe\StripeClient($stripeSecret);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            $responseData = [
                'payment_intent' => $paymentIntent,
            ];

            $paymentMethodId = $paymentIntent->payment_method ?? null;

            if ($paymentMethodId) {
                $paymentMethodDetails = $stripe->paymentMethods->retrieve($paymentMethodId);
                $responseData['payment_method_details'] = $paymentMethodDetails;
            }

            $latestChargeId = $paymentIntent->latest_charge ?? null;

            if ($latestChargeId) {
                Stripe::setApiKey('sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb');
                $charge = Charge::retrieve($latestChargeId);
                $responseData['charge_details'] = $charge;

                $balanceTransactionId = $charge->balance_transaction ?? null;

                if ($balanceTransactionId) {
                    $balanceTransactionDetails = $stripe->balanceTransactions->retrieve($balanceTransactionId);
                    $responseData['balance_transaction'] = $balanceTransactionDetails;
                }
            }

            return ApiResponse::success($responseData, 'Payment intent, charge, and method details retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
    

    public static function capturePayment($paymentIntent){
        $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $captureResponse = $stripe->paymentIntents->capture($paymentIntent);

        // Log the payment capture response
        \Log::info(sprintf(
            'Stripe payment capture response for payment intent ID %s (Attempt ID: %s): %s',
            $paymentIntent,
            json_encode($captureResponse)
        ));
    }
    public static function cancellPayment($paymentIntent){
        $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $cancellResponse = $stripe->paymentIntents->cancel($paymentIntent);

        // Log the payment capture response
        \Log::info(sprintf(
            'Stripe payment cancell response for payment intent ID %s (Attempt ID: %s): %s',
            $paymentIntent,
            json_encode($cancellResponse)
        ));
    }
    public function cancelPayment(Request $request)
    {   
        $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';
        $paymentId = $request->query('payment_id');

        if (!$paymentId) {
            return response()->json(['error' => 'Missing payment_id'], 400);
        }

        // Find the attempt by payment_id
        $attempt = Attempt::where('payment_id', $paymentId)->first();

        if (!$attempt || !$attempt->checkout_id) {
            return response()->json(['error' => 'Checkout session not found for this payment_id'], 404);
        }

        try {
            Stripe::setApiKey($stripeSecret);

            // Expire the Checkout Session
            $expiredSession = Session::expire($attempt->checkout_id);

            return response()->json([
                'success' => true,
                'message' => 'Checkout session expired successfully.',
                'session_status' => $expiredSession->status,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    public static function expireSession($cs){
        $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $expireResponse = $stripe->checkout->sessions->expire($cs);

        // Log the payment capture response
        \Log::info(sprintf(
            'Stripe payment cancell response for payment intent ID %s (Attempt ID: %s): %s',
            $cs,
            json_encode($rexpireResponse)
        ));
    }
}

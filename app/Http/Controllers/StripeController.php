<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Stripe\StripeClient;

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
    public function getPaymentIntent(Request $request)
    {
        try {
            // Validate query parameter
            $paymentIntentId = $request->query('q');
            if (!$paymentIntentId) {
                return ApiResponse::error('Payment intent ID (q) is required', 400);
            }

            // Stripe secret key
            $stripeSecret = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';

            // Initialize Stripe client
            $stripe = new \Stripe\StripeClient($stripeSecret);

            // Retrieve the payment intent
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            // Initialize response data
            $responseData = [
                'payment_intent' => $paymentIntent,
            ];

            // Check if the payment intent includes a payment method ID
            $paymentMethodId = $paymentIntent->payment_method ?? null;

            if ($paymentMethodId) {
                // Use Stripe client to retrieve payment method details
                $paymentMethodDetails = $stripe->paymentMethods->retrieve($paymentMethodId);

                // Append payment method details to the response
                $responseData['payment_method_details'] = $paymentMethodDetails;
            }

            // Check if the payment intent includes a latest charge ID
            $latestChargeId = $paymentIntent->latest_charge ?? null;

            if ($latestChargeId) {
                // Retrieve the charge details
                $charge = $stripe->charges->retrieve($latestChargeId);

                // Append the charge details to the response
                $responseData['charge_details'] = $charge;
				
				// Check if the payment intent includes a balance transaction
				$balance_transactionId = $charge->balance_transaction ?? null;

				if ($balance_transactionId) {
					// Use Stripe client to retrieve payment method details
					$balanceTransactionDetails = $stripe->balanceTransactions->retrieve($balance_transactionId);

					// Append payment method details to the response
					$responseData['balance_transaction'] = $balanceTransactionDetails;
				}
            }
            
            // Return the response
            return ApiResponse::success($responseData, 'Payment intent, charge, and method details retrieved successfully');
        } catch (\Exception $e) {
            // Catch and return any errors
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function capturePayment($paymentIntent){
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
    public function cancellPayment($paymentIntent){
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
}

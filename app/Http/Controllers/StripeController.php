<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class StripeController extends Controller
{

    public function getPaymentIntent(Request $request)
    {
        // Validate the query parameter
        $validated = $request->validate([
            'q' => 'required|string',
        ]);

        $paymentIntentId = $validated['q'];

        try {
            // Initialize the Stripe client
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            // Retrieve the payment intent
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);

            return response()->json([
                'success' => true,
                'data' => $paymentIntent,
            ]);
        } catch (\Exception $e) {
            // Handle errors (e.g., invalid payment intent ID)
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function getReceiptUrl(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string',
        ]);

        $paymentIntentId = $validated['q'];

        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);

            // Check if the payment intent contains charges
            if (!isset($paymentIntent->charges->data[0])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No charges found for this payment intent.',
                ], 404);
            }

            $receiptUrl = $paymentIntent->charges->data[0]->receipt_url;

            return response()->json([
                'success' => true,
                'receipt_url' => $receiptUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function handleWebhook(Request $request)
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['message' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                // Handle the checkout session completion
                break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // Handle the successful payment intent
                break;

            // Add more event types as needed
            default:
                Log::info('Received unknown event type ' . $event->type);
        }

        return response()->json(['message' => 'Webhook handled'], 200);
    }


}
?>


<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use App\Helpers\ApiResponse;

class StripeController extends Controller
{
    public function getPaymentIntent($paymentIntentId)
    {
        // Prepare the URL for the Stripe payment intent
        $url = "https://api.stripe.com/v1/payment_intents/{$paymentIntentId}";

        // Prepare the cURL request to Stripe API
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb:'); // Replace with your secret key

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return response()->json(['error' => curl_error($ch)], 500);
        }

        // Close the cURL session
        curl_close($ch);

        // Return the response to the client
        return response()->json(json_decode($response), 200);
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


<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class StripeController extends Controller
{

    public function getPaymentIntent(Request $request)
{
    $validated = $request->validate([
        'q' => 'required|string',
    ]);

    $paymentIntentId = $validated['q'];
    $stripeApiKey = 'sk_test_51Ll0SlL1sFOlxHWWCPqAKdMXnFb9ZdBNm1arMMoKEQ9dgxUkiTfVH7C97or4VcziWtKDTICsV3FFTCl6SS7khK8v00Tn4lEZKb';

    $url = "https://api.stripe.com/v1/payment_intents/{$paymentIntentId}";

    // Initialize cURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "{$stripeApiKey}:",
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    ]);

    // Execute and handle the response
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return response()->json([
            'success' => false,
            'error' => $error,
        ], 400);
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['error'])) {
        return response()->json([
            'success' => false,
            'error' => $responseData['error']['message'] ?? 'Unknown error',
        ], 400);
    }

    return response()->json([
        'success' => true,
        'data' => $responseData,
    ]);
}


    public function getReceiptUrl(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string',
        ]);

        $paymentIntentId = $validated['q'];

        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            // Check if charges exist and are not empty
        if (isset($paymentIntent->charges->data) && count($paymentIntent->charges->data) > 0) {
            $receiptUrl = $paymentIntent->charges->data[0]->receipt_url;

            return response()->json([
                'success' => true,
                'receipt_url' => $receiptUrl,
            ]);
        }

        // Handle cases where no charges are present
        return response()->json([
            'success' => false,
            'error' => 'No charges found for this payment intent.',
        ], 404);
        
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


<?php
namespace App\Http\Controllers;
use App\Models\FlightTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Mail\SendPass;
use App\Models\Traveler;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderTraveler;
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\TourController;
use App\Mail\BookingMail;
use App\Models\ActionLog;

class OtherPackageFunctions extends Controller
{

    /**
     * Check tourradar status.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param int $attemptId Attempt ID
     * @return array
     */
public function checkTourradarStatus($attemptId){

$attempt = DB::table('attempts')->where('id', $attemptId)->first();

if ($attempt) {
    // Process the stored data from the attempt
    $ResponseTour = json_decode($attempt->tourradar_res, true);
    $tBookingId = $ResponseTour['id'];
   // Log::info(' $tBookingId: ' . json_encode($tBookingId));
    $RequestFlight = json_decode($attempt->flight, true);

$statusResponse = TourRadarController::checkBooking($tBookingId);
Log::info('status Response: ' . json_encode($statusResponse));

if(isset($statusResponse['status']) && $statusResponse['status']=="confirmed") {


$flightResponse = bookFlight($RequestFlight);

    DB::table('attempts')
        ->where('id', $attemptId)
        ->update([
        //  'booking_id' => $order->booking_id,
            'tourradar_res' => json_encode($statusResponse),
            'duffel_res' => json_encode($flightResponse),
            'status' => 'completed',
            'updated_at' => now(),
        ]);

    try {
        // Attempt to capture the payment
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $captureResponse = $stripe->paymentIntents->capture($session->payment_intent);

        // Log the payment capture response
        \Log::info(sprintf(
            'Stripe payment capture response for payment intent ID %s (Attempt ID: %s): %s',
            $session->payment_intent,
            $attemptId,
            json_encode($captureResponse)
        ));
        // Booking successful, update the attempt record
        DB::table('orders')
        ->where('booking_id', $order->booking_id)
        ->update([
            'payment_id' => $session->payment_intent,
            'updated_at' => now(),
        ]);
        // Send the booking confirmation email
        $emailResponse = TourController::emailBConfirmation($order->booking_id);

        // Log the email response
        \Log::info('emailBConfirmation response for booking ID ' . $order->booking_id . ': ' . json_encode($emailResponse));

    } catch (\Exception $e) {
        // Handle errors during payment capture or email sending
        \Log::error('Error during payment capture or email confirmation for attempt ID ' . $attemptId . ': ' . $e->getMessage());
    }
} else {
   // Log::info('Booking reference or data key is missing in flightResponse');
}
}
}

    /**
     * Process pending attempts.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @return array
     */ 
public function processPendingAttempts() {
    $pendingAttempts = DB::table('attempts')
        ->where('status', 'pending')
        ->where('expiration', '>=', now())
        ->get();

    foreach ($pendingAttempts as $attempt) {
        $ResponseTour = json_decode($attempt->tourradar_res, true);
        $tBookingId = $ResponseTour['id'];
       // Log::info('Processing booking ID: ' . $tBookingId);

        $statusResponse = TourRadarController::checkBooking($tBookingId);
       // Log::info('Status response for booking ID ' . $tBookingId . ': ' . json_encode($statusResponse));

        if (isset($statusResponse['status']) && $statusResponse['status'] == "confirmed") {
            // Update the attempt status to confirmed
            DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
           // Log::info('Booking ID ' . $tBookingId . ' confirmed.');
        }
    }
}  

    /**
     * Update duffel order.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
public function updateDuffelOrder(Request $r)
{
    $event = $r->input('type');
    $session = $r->input('data.object');
    if ($event === 'checkout.session.completed' && isset($session['metadata']['payment_type']) && $session['metadata']['payment_type'] === 'baggage') {
        $order = $this->getOrderDetails($r->order_id);
        $ids= $this->getOfferIds($order['data']['offer_id']);
        $addServices=[];
        if ($r->checked > 0) {
            $addServices[] = [
                'quantity' => $r->checked,
                'id' => $ids[0]['baggage'],
            ];
        }

        $body = [
            'data' => [
                'payment' => [
                    'type' => 'balance',
                    'currency' => 'USD',
                    'amount' =>((double) $ids[0]['total_amount']*$r->checked).'',
                ],
                'add_services' => $addServices,
            ]
        ];
        /* return $body; */

            ActionLog::create([
                'user_id' => $r->user_id,
                'type' => 'Update',
                'action' => 'Order updated successfully',
                'item' => 'Order',
            ]);


        $url = "https://api.duffel.com/air/orders/{$r->order_id}";
        $response = Http::withHeaders($this->getDuffelHeaders())->post($url, $body);

        if ($response->failed()) {
            return response()->json(['status'=>false,'error' => $response->json()]);
        }

        return  response()->json(['status'=>true,'response'=>$response->json()]);
    }


}

    /**
     * Order services.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
public function OrderServices(Request $r){
    try{
        $url = "https://api.duffel.com/air/orders/{$r->order_id}/available_services";
        $response = Http::withHeaders($this->getDuffelHeaders())->get($url);
        $response=$response->json();
        $response=json_decode($this->order_e,true);
        $list=[];
        foreach( $response['data'] as $service){
            if($service['type']=='baggage'){
                $list[]=['baggage_id'=>$service['id'],'total_amount'=>$service['total_amount']];
            }
        }
        return  response()->json(['status'=>count($list)?true:false,'response'=>$list]);
    }catch(Exception $e){
        return  response()->json(['status'=>false,'response'=>$e->getMessage()]);
    }
}

    /**
     * Valid baggage.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $value Value
     * @return array
     */
public function validBaggage($value){
    try{
        $offerId = $value;
        $url = "https://api.duffel.com/air/offers/{$offerId}?return_available_services=true";

        $response = Http::withHeaders($this->getDuffelHeaders())->get($url);
        return $response->json();
        /* $offer['data']['owner']['iata_code']; */

        return response()->json(['status'=>true,'response'=>true]);
    }catch(Exception $e){
        return response()->json(['status'=>false,'response'=>$e->getMessage()]);
    }
}

    /**
     * Get duffel headers.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @return array
     */
private function getDuffelHeaders(){
    return [
        'Authorization' => 'Bearer ' . config('services.duffel.secret'),
        'Duffel-Version' => 'v1',
        'Content-Type' => 'application/json'
        ];
    }

    /**
     * Get order details.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $order_id Order ID
     * @return array         
     */
public function getOrderDetails($order_id){
$url = 'https://api.duffel.com/air/orders/'.$order_id;
$response = Http::withHeaders($this->getDuffelHeaders())->get($url);
return $response->json();
}

    /**
     * Get offer ids.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $order_id Order ID
     * @return array         
     */
public function getOfferIds($order_id){
    $url = "https://api.duffel.com/air/orders/{$order_id}/available_services";
    $response = Http::withHeaders($this->getDuffelHeaders())->get($url);
    $response=$response->json();
    $list=[];
    foreach( $response['data'] as $service){
        if($service['type']=='baggage'){
            $list[]=['baggage'=>$service['id'],'total_amount'=>$service['total_amount']];
        }
    }
    return $list;
}


    /**
     * Create baggage checkout session.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
public function createBaggageCheckoutSession(Request $r){
    try{
        $stripeSecret = config('services.stripe.secret');
        $urlAppFront = config('services.stripe.urlAppFront');

        Stripe::setApiKey($stripeSecret);
        $baggageType = $r->baggage_type;
        $baggageQuantity = $r->input('quantity', 1);
        $amount = $r->price * 100;
         $newUrl = $urlAppFront . "/my-trips/order?stripe_pay=true";
          $response = $this->createCheckoutSessionInternal(
            ucfirst($baggageType) . ' Baggage',
            ucfirst($baggageType) . ' baggage purchase',
            $amount * $baggageQuantity,
            $newUrl,
            [
               'metadata' => [
                'order_id' => $r->order_id,
                'passenger_id' => $r->passenger_id,
                'checked' => $r->checked,
                ]
            ]
        );

        if (isset($response['error'])) {
            return response()->json(['status'=>false,'position'=>'stripe','response' => $response['error']]);
        }
        return response()->json(['status'=>true, 'url' => $response['url']]);

    }catch(Exception $e){
        return response()->json(['status' => false,'response'=>$e->getMessage()]);
    }
}

    /**
     * Book flight.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $flight Flight
     * @return array
     */
public function bookFlight($flight){
    $flightBody = $flight;
    $flightResponse = DuffelApiController::createNewBooking($flightBody);

   // Log::info('bookFlight duffel response: ' . json_encode($flightResponse));

    if(isset($flightResponse['errors']) && $flightResponse['errors']){
        $status = 2;
    }
    return $flightResponse;
}

    /**
     * Confirm flight.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $flight Flight
     * @return array
     */
public function confirmFlight($flight){
    $flightBody = $flight;
    $flightResponse = DuffelApiController::payBooking($flightBody);

   // Log::info('confirmFlight duffel response: ' . json_encode($flightResponse));

    if(isset($flightResponse['errors']) && $flightResponse['errors']){
        $status = 2;
    }
    return $flightResponse;
}
}
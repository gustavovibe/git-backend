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
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
class PackageController extends Controller
{

    /**
     * Create checkout session.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function createCheckoutSession(Request $request)
    {
        $stripeSecret = config('services.stripe.secret');
        $urlAppFront = config('services.stripe.urlAppFront');

        Stripe::setApiKey($stripeSecret);

        $RequestFlight = $request->input('flight');
        $RequestTour = $request->input('tour');

        $tour_id = (int)$RequestTour['tour_id'];
        $tour_name = $RequestTour['tour_name'];
        $tour_desc = $RequestTour['description'];

        $rawAmount = round($request->input('price_total'), 2);
        $amount = $rawAmount * 100;

        $url = $request->url;
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);

        $newUrl = $urlAppFront . '/confirmation?' . http_build_query($queryParams);
        $tour = Tour::find($tour_id);
        if($tour){
            \Log::info('tour found: ' . $tour->tour_name);
            // Call the function and get the response
            $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount, $newUrl, $url, $RequestTour, $RequestFlight);

        }else{
            \Log::info('tour not found on db, id: ' . $tour_id);
            $response = $this->createCheckoutSessionInternal($tour_name, $tour_desc, $amount, $newUrl, $url, $RequestTour, $RequestFlight);
        }
        // Check if an error occurred
        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 400);
        }

        // Return the session URL and attempt ID
        return response()->json(['url' => $response['url'], 'attempt_id' => $response['attempt_id']]);
    }

private function createCheckoutSessionInternal($productName, $productDescription, $amount, $newUrl, $url, $RequestTour, $RequestFlight)
{
    try {

        // Insert the data into the 'attempts' table and get the newly created id
        $attemptId = DB::table('attempts')->insertGetId([
            'tour' => json_encode($RequestTour),
            'flight' => json_encode($RequestFlight),
            'new_url' => $newUrl,
            'url' => $url,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $attemptUrl = $newUrl . '&attempt_id=' . $attemptId;
        // Create the Stripe session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $productName,
                        'description' => $productDescription,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'attempt_id' => $attemptId,
            ],
            'mode' => 'payment',
            'payment_intent_data' => ['capture_method' => 'manual'],
            'success_url' => $attemptUrl,
            'cancel_url' => $url,
            'payment_method_options' => [
                'card' => [
                    'setup_future_usage' => 'off_session',
                ],
            ],
        ]);

        // Return the session URL and attempt ID
        return ['url' => $session->url, 'attempt_id' => $attemptId];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

    /**
     * Book package.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param array $tour Tour
     * @param array $flight Flight
     * @return array
     */
    public function bookPackage($tour, $flight)
    {

        $tourBody = $tour;
        if (isset($tourBody['description'])) {
            unset($tourBody['description']);
        }
        if (isset($tourBody['tour_id'])) {
            unset($tourBody['tour_id']);
        }
        if (isset($tourBody['tour_name'])) {
            unset($tourBody['tour_name']);
        }

        // Proceed with the API call
        $tourResponse = TourRadarController::createNewBooking($tourBody);

        // Log both tour and flight responses
        // Log::info('bookPackage Tour response: ' . json_encode($tourResponse));

        if(isset($tourResponse['error']) && $tourResponse['error']){
            $status = 1;
        }

        $tBookingId = $tourResponse['id'];

       // Log::info('tourradar booking id: ' . json_encode($tBookingId));

        sleep(15);

        $statusResponse = TourRadarController::checkBooking($tBookingId);

       // Log::info('status Response: ' . json_encode($statusResponse));

        if(isset($statusResponse['status']) && $statusResponse['status']=="confirmed") {

        $flightBody = $flight;
        $flightResponse = DuffelApiController::createNewBooking($flightBody);

       // Log::info('duffel response: ' . json_encode($flightResponse));

        if(isset($flightResponse['errors']) && $flightResponse['errors']){
            $status = 2;
        }

        if (isset($flightResponse['data']) && isset($flightResponse['data']['booking_reference'])) {

        $passengers = $tourResponse['passengers'];

        $firstIteration = true;

        $mainPassenger = "";

        $mainPassengerCountry = "";

        $mainPassengerAge = 0;

        $groupSize = count($tourBody['passengers']);
        $traveler_id=0;

        $firstIteration = true;
        foreach ($passengers as $passenger) {


                $mainPassenger = $passenger['fields']['title']=='Mr.'?'male':'female';

                $mainPassengerCountry = $passenger['fields']['place_of_issue'];

                $random= Str::random(12);
                $u= User::where('email',$passenger['fields']['email'])->first();
                $user=$u?$u: new User;
                if(!$u){
                    $phone = $passenger['fields']['phone_number'];
                    $countryCode='';
                    $localNumber='';
                    $parsedPhone = PhoneNumber::parse($phone);
                    if($parsedPhone->format(PhoneNumberFormat::E164)){
                        $countryCode = $parsedPhone->getCountryCode();
                        $localNumber = $parsedPhone->getNationalNumber();
                    }else{
                        $localNumber= $passenger['fields']['phone_number'];
                    }
                    $user->fill([
                        'name' => $passenger['fields']['first_name'] . " " . $passenger['fields']['last_name'],
                        'password' => Hash::make($random),
                        'profile_id' => 2,
                        'phone' =>$localNumber,
                        'phone_country' => $countryCode,
                        'country' => $passenger['fields']['place_of_issue'],
                        'role' => 'role',
                        'active' => 1,
                        'suscribed' => 1,
                        'hear' => "without comment",
                        ]);

                        Mail::to($user->email)->send(new SendPass(['name'=>$passenger['fields']['first_name'],'password'=>$random, "email" => $user->email]));
                }

                $traveler=Traveler::updateOrCreate(
                    ['mail'=>$passenger['fields']['email']],
                    [
                        'title'=>$passenger['fields']['title'],
                        'gender'=> $passenger['fields']['title']=='Mr.'?'male':'female',
                        'name'=>$passenger['fields']['first_name'],
                        'last'=>$passenger['fields']['last_name'],
                        'birth'=>Carbon::createFromFormat('d/m/Y',$passenger['fields']['date_of_birth']),
                        'passport'=>$passenger['fields']['passport_number'],
                        'country'=>$passenger['fields']['place_of_issue'],
                        'place'=>$passenger['fields']['place_of_issue'],
                        'issue'=>Carbon::createFromFormat('d/m/Y',$passenger['fields']['issue_date']),
                        'expire'=>Carbon::createFromFormat('d/m/Y',$passenger['fields']['expiration_date']),
                        'phone'=>$passenger['fields']['phone_number'],
                        'address'=>isset($passengers[0]['fields']['address'])? $passengers[0]['fields']['address']:'n/a',
                        'user_id'=>$user->id,
                        'status'=>1,
                    ]);

                $traveler_id=$traveler->traveler_id;
                $dob = Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth']);

                $today = Carbon::now();

                $mainPassengerAge = $dob->diffInYears($today);

                if ($firstIteration) {
                    ActionLog::create([
                        'user_id' => $u->id,
                        'type' => 'Create',
                        'action' => 'Order created successfully',
                        'item' => 'Order',
                    ]);
                    $firstIteration = false;
                }
        }


        function convertDurationToMinutes($duration)
        {
            try {
                $interval = new \DateInterval($duration);
                $minutes = $interval->days * 1440 + $interval->h * 60 + $interval->i;
                return $minutes;
            } catch (\Exception $e) {
                return 0;
            }
        }

        $departure1 = Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['departing_at']);
        $arrival1 = Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['arriving_at']);

        $departure2 = Carbon::parse($flightResponse['data']['slices'][1]['segments'][0]['departing_at']);
        $arrival2 = Carbon::parse($flightResponse['data']['slices'][1]['segments'][0]['arriving_at']);


        $duration1_in_minutes = $arrival1->diffInMinutes($departure1);

        $duration2_in_minutes = $arrival2->diffInMinutes($departure2);

        $total_duration_in_minutes = $duration1_in_minutes + $duration2_in_minutes;

        $total_hours = floor($total_duration_in_minutes / 60);

        $remaining_minutes = $total_duration_in_minutes % 60;

        $total_days = $total_hours / 24;

        $total_days_with_tour = $total_days + $tourResponse['tour']['tour_length_days'];


        $tripDuration = '';

        switch (true) {
            case ($total_days_with_tour >= 1 && $total_days_with_tour <= 3):
                $tripDuration = '1';
                break;
            case ($total_days_with_tour >= 4 && $total_days_with_tour <= 10):
                $tripDuration = '2';
                break;
            case ($total_days_with_tour >= 11 && $total_days_with_tour <= 15):
                $tripDuration = '3';
                break;
            case ($total_days_with_tour >= 16 && $total_days_with_tour <= 20):
                $tripDuration = '4';
                break;
            case ($total_days_with_tour >= 21 && $total_days_with_tour <= 25):
                $tripDuration = '5';
                break;
            case ($total_days_with_tour >= 26 && $total_days_with_tour <= 30):
                $tripDuration = '6';
                break;
            case ($total_days_with_tour >= 31):
                $tripDuration = '7';
                break;
            default:
                $tripDuration = '0';
                break;
        }

        $tour_length = $tourResponse['tour']['tour_length_days'];

        $adventureDuration = '';

        switch (true) {
            case ($tour_length >= 1 && $tour_length <= 2):
                $adventureDuration = '1';
                break;
            case ($tour_length >= 3 && $tour_length <= 5):
                $adventureDuration = '2';
                break;
            case ($tour_length >= 6 && $tour_length <= 10):
                $adventureDuration = '3';
                break;
            case ($tour_length >= 11 && $tour_length <= 15):
                $adventureDuration = '4';
                break;
            case ($tour_length >= 16 && $tour_length <= 20):
                $adventureDuration = '5';
                break;
            case ($tour_length >= 21):
                $adventureDuration = '6';
                break;
            default:
                $adventureDuration = '0';
                break;
        }


        $ageGroup = '';

        switch (true) {
            case ($mainPassengerAge >= 18 && $mainPassengerAge <= 24):
                $ageGroup = '1';
                break;
            case ($mainPassengerAge >= 25 && $mainPassengerAge <= 34):
                $ageGroup = '2';
                break;
            case ($mainPassengerAge >= 35 && $mainPassengerAge <= 44):
                $ageGroup = '3';
                break;
            case ($mainPassengerAge >= 45 && $mainPassengerAge <= 54):
                $ageGroup = '4';
                break;
            case ($mainPassengerAge >= 55 && $mainPassengerAge <= 64):
                $ageGroup = '5';
                break;
            case ($mainPassengerAge >= 65):
                $ageGroup = '6';
                break;
            default:
                $ageGroup = '0';
                break;
        }
        $tour=Tour::where('tour_id',$tourResponse['tour']['tour_id'])->select('tour_id','commission')->first();
        $orderData = [
            'departure' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['departing_at'])->format('Y-m-d'),
            'start' => $tourResponse['departure_date'],
            'arrival' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['arriving_at'])->format('Y-m-d'),
            'end' => $tourResponse['return_date'],
            'duration' => convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),
            'tour_length' => $adventureDuration,
            'tour_name' => $tourResponse['tour']['tour_name'],
            'tour_id' => $tourResponse['tour']['tour_id'],
            'commission' => $tour->commission,
            'operator' => $tourResponse['tour']['operator']['id'],
            'start_city' => $flightResponse['data']['slices'][0]['origin']['city_name'],
            'end_city' => $flightResponse['data']['slices'][0]['destination']['city_name'],
            'booking_status' => $tourResponse['status'],
            'tourradar_id' => $tourResponse['id'],
            'tourradar_status' => $tourResponse['status'],
            'tourradar_reason' => $tourResponse['status_reason']? $tourResponse['status_reason']:'travel',
            'tourradar_text' => $tourResponse['status_reason_text']?$tourResponse['status_reason_text']:'n/a',
            'duffel_id' => $flightResponse['data']['id'],
            'origin' => $flightResponse['data']['slices'][0]['origin']['iata_code'],
            'f_destination' => $flightResponse['data']['slices'][0]['destination']['iata_code'],
            'f_return' => $flightResponse['data']['slices'][1]['destination']['iata_code'],
            'f_duration' => convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),
            'destination_stops' => count($flightResponse['data']['slices'][0]['segments']),
            'return_stops' => count($flightResponse['data']['slices'][1]['segments']),
            'total_stops' => count($flightResponse['data']['slices'][0]['segments']) + count($flightResponse['data']['slices'][1]['segments']),
            'destination_carrier' => $flightResponse['data']['slices'][0]['segments'][0]['operating_carrier']['name'],
            'return_carrier' => $flightResponse['data']['slices'][1]['segments'][0]['operating_carrier']['name'],
            'checked_bags' => $flightResponse['data']['slices'][0]['segments'][0]['passengers'][0]['baggages'][0]['quantity'],
            'travelers_number' => count($tourResponse['passengers']),
            'reference' => $flightResponse['data']['booking_reference'],
            'currency' => $tourResponse['currency'],
            'paid' => $tourResponse['total_value'] + $flightResponse['data']['total_amount'],
            'p_flight' => $flightResponse['data']['total_amount'],
            'p_tour' => $tourResponse['total_value'],
            'commission_value_tour' => $tourResponse['partner_info']['commission_value'],
            'discounted' => $tourResponse['promotions'][0]['prices'][0]['price_per_pax'] ?? null,
            'promo' => $tourResponse['promotions'][0]['id'] ?? null,
            'user_id' => $user->id,
            'whole_trip' => $tripDuration,
            'channel' => 'web',
            'payment_method' => 'card_and_wallet',
            'medium' => 'desktop',
            'gender' => $mainPassenger,
            'age_group' => $ageGroup,
            'group_size' => $groupSize,
            'country' => $mainPassengerCountry,
            'carrier' => $flightResponse['data']['owner']['name']
        ];

        $order = Order::create($orderData);
        if($order){
            TourController::emailBConfirmation($order->booking_id,$order->duffer_id);
        }
        OrderTraveler::create(['booking_id'=>$order->booking_id,'traveler_id'=>$traveler_id]);
        try {
            if ($order && $order->booking_id) {
                $order->flightTour()->create([
                    'flight' => $flightResponse,
                    'tour' => $tourResponse,
                ]);
            } else {
                throw new \Exception('Order could not be created.');
            }
        } catch (\Exception $e) {
            \Log::error('Error creating flight tour: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        $status = 0;
        } else {
           // Log::info('Booking reference or data key is missing in flightResponse');
        }
        }else{
            $status = 1;
            $flightResponse = "not_requested";
            $order = "not_created-tbooking status pending";
        }
        return [$status, $statusResponse, $flightResponse, $order];
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
     * Checkout webhook.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
public function checkoutWebhook(Request $request)
{
    // Set Stripe secret key
    $stripeSecret = config('services.stripe.secret');
    Stripe::setApiKey($stripeSecret);

    // Webhook secret
    $endpointSecret = 'whsec_lvpw37kpWipUbi3iQT8N4kMXI3sGxOcx';

    // Retrieve the payload and signature header
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $event = null;

    try {
        // Construct the event from the payload and header
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sigHeader, $endpointSecret
        );
    } catch (\UnexpectedValueException $e) {
        // Invalid payload
        \Log::error('Error parsing payload: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid payload'], 400);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        \Log::error('Error verifying webhook signature: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;

            // Get the attempt ID from the session metadata
            $attemptId = $session->metadata->attempt_id ?? null;

            if ($attemptId) {
                // Retrieve the attempt record from the database
                $attempt = DB::table('attempts')->where('id', $attemptId)->first();

                if ($attempt) {
                    // Process the stored data from the attempt
                    $RequestTour = json_decode($attempt->tour, true);
                    $RequestFlight = json_decode($attempt->flight, true);

                    // Log the start of the booking process
                    \Log::info('Starting booking process for attempt ID: ' . $attemptId);

                    // Execute the booking process
                    $response = $this->bookPackage($RequestTour, $RequestFlight);
                    // Log the start of the booking process
                    \Log::info('Response (general): ' . json_encode($response));
                    // Extract the responses
                    $status = $response[0];
                    $tourResponse = $response[1] ?? null;
                    $flightResponse = $response[2] ?? null;
                    $order = $response[3] ?? null;
                    // Log both tour and flight responses
                    \Log::info('Tour response for attempt ID ' . $attemptId . ': ' . json_encode($tourResponse));
                    \Log::info('Flight response for attempt ID ' . $attemptId . ': ' . json_encode($flightResponse));
                    // Log both tour and flight responses
                    //\Log::info('status ' . $attemptId . ': ' . $status);

                    if (intval($status) == 0) {
                        // Booking successful, update the attempt record
                        \Log::info('status0' . $attemptId . ': ' . $status);

                        DB::table('attempts')
                            ->where('id', $attemptId)
                            ->update([
                                'booking_id' => $order->booking_id,
                                'tourradar_res' => json_encode($tourResponse),
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
                    }

                    if (intval($status) > 0) {
                        \Log::info('status1-2' . $attemptId . ': ' . $status);
                        // Booking failed, update the attempt record
                        // \Log::error('Booking package failed for attempt ID ' . $attemptId . ': ' . json_encode([$tourResponse, $flightResponse]));

                        DB::table('attempts')
                            ->where('id', $attemptId)
                            ->update([
                                'status' => 'failed',
                                'tourradar_res' => json_encode($tourResponse),
                                'duffel_res' => json_encode($flightResponse),
                                'updated_at' => now(),
                            ]);
                    }
                } else {
                    // Attempt record not found
                    \Log::error('Attempt not found for ID: ' . $attemptId);
                }
            } else {
                // No attempt ID found in the session metadata
                \Log::error('No attempt ID found in session metadata.');
            }

            break;
        default:
            // Log unknown event type
            \Log::warning('Received unknown event type: ' . $event->type);
            return response()->json(['error' => 'Unhandled event type'], 400);
    }

    // Return a 200 response for handled events
    return response()->json(['status' => 'success'], 200);
}


    /**
     * Check booking status.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
public function checkBookingStatus(Request $request)
{
    $attemptId = $request->attempt_id;

    $attempt = DB::table('attempts')->where('id', $attemptId)->first();

    if ($attempt && $attempt->booking_id) {
        return response()->json(['status' => 'completed', 'booking_id' => $attempt->booking_id]);
    }

    return response()->json(['status' => 'pending']);
}

}

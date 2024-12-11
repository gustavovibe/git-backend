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

class NewPackageController extends Controller
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
        $expiration = $request->input('expiration');

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
            $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration);

        }else{
            \Log::info('tour not found on db, id: ' . $tour_id);
            $response = $this->createCheckoutSessionInternal($tour_name, $tour_desc, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration);
        }
        // Check if an error occurred
        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 400);
        }

        // Return the session URL and attempt ID
        return response()->json(['url' => $response['url'], 'attempt_id' => $response['attempt_id']]);
    }

    /**
     * Create checkout session internal.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $productName Product name
     * @param string $productDescription Product description
     * @param float $amount Amount
     * @param string $newUrl New URL
     * @param string $url URL
     * @param array $RequestTour Request tour
     * @param array $RequestFlight Request flight
     * @param string $expiration Expiration
     * @return array
     */
private function createCheckoutSessionInternal($productName, $productDescription, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration)
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
            'expiration' => $expiration,
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
            'payment_method_types' => [
                'link',
                'card',
                'affirm',
                'afterpay_clearpay',
                'alma',
                'billie',
                'capchase_pay',
                'klarna',
                'kriya',
                'mondu',
                'sequra',
                'mobilepay',
                'paypal',
                'revolut_pay',
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
        $order = null;

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

        // Log tour response
        // Log::info('bookPackage Tour response: ' . json_encode($tourResponse));

        if(isset($tourResponse['error']) && $tourResponse['error']){
            $status = 1;
        }else {
            $tBookingId = $tourResponse['id'];
            Log::info('tourradar booking id: ' . json_encode($tBookingId));

            Log::info('bookPackage duffel request: ' . json_encode($flight));
            $flightResponse = DuffelApiController::createNewBooking($flight);
    
            Log::info('bookPackage duffel response: ' . json_encode($flightResponse));
    
            if(isset($flightResponse['errors']) && $flightResponse['errors']){
                $status = 2;
            }
    
            elseif (isset($flightResponse['data']) && isset($flightResponse['data']['payment_status'])) {    
                $order = $this->createOrder($flightResponse, $tourBody, $tourResponse);
                Log::info('order created: ' . json_encode($order));
                $status = 0;
            }
        }
        return [$status, $tourResponse, $flightResponse, $order];
    }

    /**
     * Create order.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $flightResponse Flight response
     * @param array $tourBody Tour body
     * @param array $tourResponse Tour response
     * @return array
     */ 
    public function createOrder($flightResponse, $tourBody, $tourResponse){

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
    
        $totalDaysWithTour = $total_days + $tourBody['tour']['tour_length_days'];
        $tripDuration = $this->calculateTripDuration($totalDaysWithTour);
    
        $tourLength = $tourBody['tour']['tour_length_days'];
        $adventureDuration = $this->calculateAdventureDuration($tourLength);
    
        $mainPassengerAge = $tourBody['main_passenger']['age'];
        $ageGroup = $this->determineAgeGroup($mainPassengerAge);
    
        $tour = Tour::where('tour_id', $tourBody['tour']['tour_id'])->select('tour_id', 'commission')->first();
    
        $orderData = [
            'departure' => $departure1->format('Y-m-d'),
            'start' => $tourResponse['departure_date'],
            'arrival' => $arrival1->format('Y-m-d'),
            'end' => $tourResponse['return_date'],
            'duration' => $this->convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),
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
            'tourradar_reason' => $tourResponse['status_reason'] ?: 'travel',
            'tourradar_text' => $tourResponse['status_reason_text'] ?: 'n/a',
            'duffel_id' => $flightResponse['data']['id'],
            'origin' => $flightResponse['data']['slices'][0]['origin']['iata_code'],
            'f_destination' => $flightResponse['data']['slices'][0]['destination']['iata_code'],
            'f_return' => $flightResponse['data']['slices'][1]['destination']['iata_code'],
            'f_duration' => $this->convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),
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

        //$mail = new BookingMail($order);
        //Mail::to($order->user->email)->send($mail);
        OrderTraveler::create(['booking_id'=>$order->booking_id,'traveler_id'=>$traveler_id]);
        try {
            if ($order && $order->booking_id) {
                $order->flightTour()->create([
                    'flight' => $flightResponse,
                    'tour' => $tourBody,
                ]);
                $this->createPassengers($tourBody);
            } else {
                throw new \Exception('Order could not be created.');
            }
        } catch (\Exception $e) {
            \Log::error('Error creating flight tour: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return $order;
    }

    /**
     * Calculate adventure duration.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param int $tourLength Tour length
     * @return string
     */
    public function calculateAdventureDuration(int $tourLength): string {
        switch (true) {
            case ($tourLength >= 1 && $tourLength <= 2):
                return '1';
            case ($tourLength >= 3 && $tourLength <= 5):
                return '2';
            case ($tourLength >= 6 && $tourLength <= 10):
                return '3';
            case ($tourLength >= 11 && $tourLength <= 15):
                return '4';
            case ($tourLength >= 16 && $tourLength <= 20):
                return '5';
            case ($tourLength >= 21):
                return '6';
            default:
                return '0';
        }
    }

    /**
     * Calculate trip duration.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param float $totalDaysWithTour Total days with tour
     * @return string
     */
    public function calculateTripDuration(float $totalDaysWithTour): string {
        switch (true) {
            case ($totalDaysWithTour >= 1 && $totalDaysWithTour <= 3):
                return '1';
            case ($totalDaysWithTour >= 4 && $totalDaysWithTour <= 10):
                return '2';
            case ($totalDaysWithTour >= 11 && $totalDaysWithTour <= 15):
                return '3';
            case ($totalDaysWithTour >= 16 && $totalDaysWithTour <= 20):
                return '4';
            case ($totalDaysWithTour >= 21 && $totalDaysWithTour <= 25):
                return '5';
            case ($totalDaysWithTour >= 26 && $totalDaysWithTour <= 30):
                return '6';
            case ($totalDaysWithTour >= 31):
                return '7';
            default:
                return '0';
        }
    }
        

    /**
     * Determine age group.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param int $mainPassengerAge Main passenger age
     * @return string     
     */
    public function determineAgeGroup(int $mainPassengerAge): string {
        switch (true) {
            case ($mainPassengerAge >= 18 && $mainPassengerAge <= 24):
                return '1';
            case ($mainPassengerAge >= 25 && $mainPassengerAge <= 34):
                return '2';
            case ($mainPassengerAge >= 35 && $mainPassengerAge <= 44):
                return '3';
            case ($mainPassengerAge >= 45 && $mainPassengerAge <= 54):
                return '4';
            case ($mainPassengerAge >= 55 && $mainPassengerAge <= 64):
                return '5';
            case ($mainPassengerAge >= 65):
                return '6';
            default:
                return '0';
        }
    }

    /**
     * Create passengers.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $tourBody Tour body
     * @return array
     */
public function createPassengers($tourBody) {
    $passengers = $tourBody['passengers'];

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
                $user->fill([
                    'name' => $passenger['fields']['first_name'] . " " . $passenger['fields']['last_name'],
                    'password' => Hash::make($random),
                    'profile_id' => 2,
                    'phone' => $passenger['fields']['phone_number'],
                    'country' => $passenger['fields']['place_of_issue'],
                    'role' => 'role',
                    'active' => 1,
                    'suscribed' => 1,
                    'hear' => "without comment",
                    ]);

                    Mail::to($user->email)->send(new SendPass(['name'=>$passenger['fields']['first_name'],'password'=>$random]));
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
}

    /**
     * Convert duration to minutes.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $duration Duration
     * @return int
     */ 
public function convertDurationToMinutes($duration)
{
    try {
        $interval = new \DateInterval($duration);
        $minutes = $interval->days * 1440 + $interval->h * 60 + $interval->i;
        return $minutes;
    } catch (\Exception $e) {
        return 0;
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
                    //$offerId = $RequestFlight['data']['selected_offers'][0];
                    //$flightOffer = DuffelApiController::getOffer($offerId);
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
                    $orderId = null;
                    if (isset($flightResponse['data'])) {
                        $orderId = $flightResponse['data']['id'] ?? null;
                    } else {
                        \Log::error('Missing key "data" in $flightResponse:', $flightResponse);
                    }
                                        
                    \Log::info('Duffel order Id: ' . $orderId);
                    // Log both tour and flight responses
                    \Log::info('Tour response for attempt ID ' . $attemptId . ': ' . json_encode($tourResponse));
                    \Log::info('Flight response for attempt ID ' . $attemptId . ': ' . json_encode($flightResponse));
                    // Log both tour and flight responses
                    //\Log::info('status ' . $attemptId . ': ' . $status);

                    if (intval($status) > 0) {
                        \Log::info('status 1-2 for attempt: ' . $attemptId . ': ' . $status);
                        // Booking failed, update the attempt record
                        // \Log::error('Booking package failed for attempt ID ' . $attemptId . ': ' . json_encode([$tourResponse, $flightResponse]));

                        DB::table('attempts')
                            ->where('id', $attemptId)
                            ->update([
                                'status' => 'failed',
                                'tourradar_res' => json_encode($tourResponse),
                                'duffel_res' => json_encode($flightResponse),
                                'order_id' => $orderId,
                                'payment_id' => $session->payment_intent,
                                'updated_at' => now(),
                            ]);
                    }else{
                        \Log::info('status 0 for attempt: ' . $attemptId . ': ' . $status);
                        DB::table('attempts')
                            ->where('id', $attemptId)
                            ->update([
                                'status' => 'pending',
                                'tourradar_res' => json_encode($tourResponse),
                                'duffel_res' => json_encode($flightResponse),
                                'order_id' => $orderId,
                                'payment_id' => $session->payment_intent,
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
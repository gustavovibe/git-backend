<?php
namespace App\Http\Controllers;
use App\Models\FlightTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Mail\SendPass;
use App\Models\Traveler;
use App\Models\Country;
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
use App\Http\Controllers\StripeController;
use App\Mail\BookingMail;
use App\Models\ActionLog;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;

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
        $passengers = $request->input('passengers');

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
            $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration, $passengers);

        }else{
            \Log::info('tour not found on db, id: ' . $tour_id);
            $response = $this->createCheckoutSessionInternal($tour_name, $tour_desc, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration, $passengers);
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
private function createCheckoutSessionInternal($productName, $productDescription, $amount, $newUrl, $url, $RequestTour, $RequestFlight, $expiration, $passengers)
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
            'passengers' => json_encode($passengers)
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
                'klarna',
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
    public function bookPackage($RequestTour, $RequestFlight, $paymentId, $attemptId, $RequestPassengers)
    {
        $order = null;
        $tourResponse = null;
        $flightResponse = null;
        $flight = $RequestFlight;
        $tourBody = $RequestTour;
        $status = 0;
        if (isset($tourBody['description'])) {
            unset($tourBody['description']);
        }
        if (isset($tourBody['tour_id'])) {
            unset($tourBody['tour_id']);
        }
        if (isset($tourBody['tour_name'])) {
            unset($tourBody['tour_name']);
        }

        Log::info('bookPackage Tour request: ' . json_encode($tourBody));

        try {
            $tourResponse = TourRadarController::createNewBooking($tourBody);
            Log::info('…got tourResponse', ['response' => $tourResponse]);
        } catch (\Throwable $e) {
            Log::error('TourRadar booking threw exception', ['message' => $e->getMessage()]);
            throw $e;  // or handle it
        }

        if(isset($tourResponse['error']) && $tourResponse['error']){
            $status = 1;

            DB::table('attempts')
                    ->where('id', $attemptId)
                    ->update([
                        'booking_id' => null,
                        'status' => intval($status) > 0 ? 'failed' : 'pending',
                        'tourradar_res' => json_encode($tourResponse),
                        'payment_id' => $paymentId,
                        'updated_at' => now(),
                    ]);

            Log::info('Tourradar error: ' . $attemptId, ['error' => $tourResponse['error']]);

        }else {
            $tBookingId = $tourResponse['id'];
            Log::info('tourradar booking id: ' . json_encode($tBookingId));

            DB::enableQueryLog();
            DB::table('attempts')
                    ->where('id', $attemptId)
                    ->update([
                        'booking_id' => null,
                        'status' => intval($status) > 0 ? 'failed' : 'pending',
                        'tourradar_res' => json_encode($tourResponse),
                        'payment_id' => $paymentId,
                        'updated_at' => now(),
                    ]);
            Log::info('Query log:', DB::getQueryLog());


            //Log::info('bookPackage duffel request: ' . json_encode($flight));
            $flightResponse = DuffelApiController::createNewBooking($flight);

            //Log::info('bookPackage duffel response: ' . json_encode($flightResponse));
            if(isset($flightResponse['errors']) && $flightResponse['errors']){
                $status = 2;
                DB::table('attempts')
                    ->where('id', $attemptId)
                    ->update([
                        'status' => intval($status) > 0 ? 'failed' : 'pending',
                        'tourradar_res' => json_encode($tourResponse),
                        'duffel_res' => $flightResponse ?? null,
                        'payment_id' => $paymentId,
                        'updated_at' => now(),
                    ]);

                Log::info('Duffel error: ' . $attemptId, ['error' => $flightResponse['errors']]);
            }

            elseif (isset($flightResponse['data']) && $flightResponse['data']['payment_status']['paid_at'] != null) {
                
                $order = $this->createOrder($flightResponse, $tourResponse, $paymentId, $RequestPassengers);
                //Log::info('order created: ' . json_encode($order));
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
     * @param array $tourResponse Tour response
     * @return array
     */
    public function createOrder($flightResponse, $tourResponse, $paymentId, $RequestPassengers){

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

        $totalDaysWithTour = $total_days + $tourResponse['tour']['tour_length_days'];
        $tripDuration = $this->calculateTripDuration($totalDaysWithTour);

        $tourLength = $tourResponse['tour']['tour_length_days'];
        $adventureDuration = $this->calculateAdventureDuration($tourLength);

        $passenger = $tourResponse['passengers'][0];
        $dob = Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth']);
        $today = Carbon::now();
        $mainPassengerAge = $dob->diffInYears($today);
        $mainPassenger = $passenger['fields']['title']=='Mr.'?'male':'female';
        $mainPassengerCountry = $passenger['fields']['place_of_issue'];

        $ageGroup = $this->determineAgeGroup($mainPassengerAge);

        $tour = Tour::where('tour_id', $tourResponse['tour']['tour_id'])->select('tour_id', 'commission')->first();

        $passengers = $tourResponse['passengers'];

        $user = $this->createUser($passenger);

        $userId = $user->id;

        $groupSize = count($tourResponse['passengers']);

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
            'paid' => ceil(($tourResponse['total_value'] + $flightResponse['data']['total_amount'])*1.15),
            'p_flight' => $flightResponse['data']['total_amount'],
            'p_tour' => $tourResponse['total_value'],
            'commission_value_tour' => $tourResponse['partner_info']['commission_value'],
            'discounted' => $tourResponse['promotions'][0]['prices'][0]['price_per_pax'] ?? null,
            'promo' => $tourResponse['promotions'][0]['id'] ?? null,
            'user_id' => $userId,
            'whole_trip' => $tripDuration,
            'channel' => 'web',
            'payment_method' => 'card_and_wallet',
            'medium' => 'desktop',
            'gender' => $mainPassenger,
            'age_group' => $ageGroup,
            'group_size' => $groupSize,
            'country' => $mainPassengerCountry,
            'carrier' => $flightResponse['data']['owner']['name'],
            'payment_id' => $paymentId,
            'stripe_fee' => null,
            'passengers' => $RequestPassengers
        ];
        //\Log::info('Order data:', $orderData);

        $order = Order::create($orderData);

        $traveler_id = $this->createTravelers($passengers, $userId);

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
    public function createUser($passenger)
    {
        $random = Str::random(12);

        // Check if the user exists
        $user = User::where('email', $passenger['fields']['email'])->first();

        // If the user doesn't exist, create a new one
        if (!$user) {
            $user = new User();
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
                'email' => $passenger['fields']['email'],
                'password' => Hash::make($random),
                'profile_id' => 2,
                'phone' => $localNumber,
                'phone_country' => $countryCode,
                'country' => $passenger['fields']['place_of_issue'],
                'role' => 'role',
                'active' => 1,
                'suscribed' => 1,
                'hear' => "without comment",
            ]);
            $user->save();
            Mail::to($user->email)->send(new SendPass(['name'=>$passenger['fields']['first_name'],'password'=>$random, 'id'=>$user->id, "email" => $passenger['fields']['email']]));

        }

        // Log the action if the user was found or created
        ActionLog::create([
            'user_id' => $user->id,
            'type' => 'Create',
            'action' => 'Order created successfully',
            'item' => 'Order',
        ]);

        return $user;
    }


public function createTravelers($passengers,$userId)
{
    $firstTravelerId = null; // Initialize the first traveler ID

    foreach ($passengers as $index => $passenger) {
        $countryId = Country::where('name',  $passenger['fields']['country'])->first()->t_country_id;
        $data = [
            'title' => $passenger['fields']['title'],
            'gender' => $passenger['fields']['title'] == 'Mr.' ? 'male' : 'female',
            'name' => $passenger['fields']['first_name'],
            'last' => $passenger['fields']['last_name'],
            'birth' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth']),
            'passport' => $passenger['fields']['passport_number'],
            //'country' => $passenger['fields']['country'],
            'country' => $countryId,          
            'place' => $passenger['fields']['place_of_issue'],
            'issue' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['issue_date']),
            'expire' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['expiration_date']),
            'phone' => preg_replace('/^\+\d{1,4}(?=\d{10}$)/', '', $passenger['fields']['phone_number']),
            'address' => isset($passenger['fields']['address']) ? $passenger['fields']['address'] : 'n/a',
            'user_id' => $userId,
            'status' => 1,
        ];
        if($index == 0){
            $traveler = Traveler::updateOrCreate(
                ['mail' => $passenger['fields']['email']],
                $data
            );
            $firstTravelerId = $traveler->traveler_id;
        }else{
            $data['mail'] = $passenger['fields']['email'];
            $traveler = Traveler::create($data);
        }
    }

    return $firstTravelerId; // Return the first traveler ID
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
        Stripe::setApiKey(config('services.stripe.secret'));

        // Webhook secret
        $endpointSecret = 'whsec_lvpw37kpWipUbi3iQT8N4kMXI3sGxOcx';

        // Retrieve the payload and signature header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = null;

        try {
            // Construct the event from the payload and header
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            \Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['status' => 'success'], 200); // Always return success
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['status' => 'success'], 200); // Always return success
        }

        // Handle the event type
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $paymentId = $session->payment_intent;
                $cs = $session->id;
                // Extract metadata
                $attemptId = $session->metadata->attempt_id ?? null;
                $status = $session->payment_status ?? null;
                \Log::info('Status: ' . $status);
                if (!$attemptId) {
                    \Log::error('No attempt ID found in session metadata.');
                    break;
                }

                // Retrieve the attempt record from the database
                $attempt = DB::table('attempts')->where('id', $attemptId)->first();
                if (!$attempt) {
                    \Log::error('Attempt not found for ID: ' . $attemptId);
                    break;
                }

                \Log::info('Processing booking for attempt ID: ' . $attemptId);

                $RequestTour = json_decode($attempt->tour, true);
                $RequestFlight = json_decode($attempt->flight, true);
                $RequestPassengers = json_decode($attempt->passengers, true);
                \Log::info('Request passengers:', $RequestPassengers); 
                \Log::info('Payment Id: ' . $paymentId);

                // Execute booking process
                $checkOrder = Order::where('payment_id', $paymentId)->first();
                if($checkOrder){
                    \Log::info('Order already exists for payment ID: ' . $paymentId);
                    break;
                }

                $response = $this->bookPackage($RequestTour, $RequestFlight, $paymentId, $attemptId, $RequestPassengers);

                // Extract responses
                $status = $response[0] ?? null;
                $tourResponse = $response[1] ?? null;
                \Log::info('stripe webhook Tour response: ' . json_encode($tourResponse));
                $flightResponse = $response[2] ?? null;
                //\Log::info('stripe webhook Flight response: ' . json_encode($flightResponse));
                $order = $response[3] ?? null;
                //\Log::info('stripe webhook Order response: ' . json_encode($order));
                $orderId = $response[2]['data']['id'] ?? null;
                \Log::info('stripe webhook OrderID response: ' . json_encode($orderId));

                $bookingId = null;

                if ($order != null) {
                    Log::info('Email send attempt from package controller');
                
                    $bookingId = json_decode($order['booking_id']);
                    $duffelId = $order['duffel_id'];
                    Log::info('Duffel ID raw value:', ['duffel_id' => $order['duffel_id']]);

                    // Log input data
                    Log::info('Booking ID:', ['booking_id' => $bookingId]);

                    $raw = $RequestPassengers; 
                    $passengersArray = is_string($raw) ? json_decode($raw, true) : $raw;

                    Log::info('Request passengers before emailBConfirmation:', $passengersArray);

                    $response = TourController::emailBConfirmation($bookingId, $duffelId, $paymentId, $passengersArray);

                    // Log the response
                    Log::info('Response from emailBConfirmation:', ['response' => $response]);
                
                    // Log manually in case email sending fails inside emailBConfirmation
                    if ($response instanceof \Illuminate\Http\JsonResponse) {
                        $responseData = $response->getData(true);
                        if ($responseData['success'] ?? false) {
                            Log::info('Email sent successfully.');
                        } else {
                            Log::error('Email sending failed.', ['error' => $responseData]);
                        }
                    } else {
                        Log::warning('Unexpected response format from emailBConfirmation.', ['response' => $response]);
                    }
                }

                DB::enableQueryLog();

                // Update database record
                DB::table('attempts')
                    ->where('id', $attemptId)
                    ->update([
                        'booking_id' => $bookingId,
                        'status' => intval($status) > 0 ? 'failed' : 'pending',
                        //'tourradar_res' => json_encode($tourResponse),
                        'duffel_res' => json_encode($flightResponse),
                        'order_id' => $orderId,
                        'payment_id' => $paymentId,
                        'checkout_session' => $cs,
                        'updated_at' => now(),
                    ]);
                
                Log::info('Query log:', DB::getQueryLog());
                            
                \Log::info('Booking process completed for attempt ID: ' . $attemptId);
                break;

            case 'checkout.session.async_payment_succeeded':
                // Future implementation for async success
                \Log::info('Async payment succeeded event received.');
                break;

            case 'checkout.session.async_payment_failed':
                // Future implementation for async failure
                \Log::info('Async payment failed event received.');
                break;

            default:
                \Log::warning('Unhandled event type: ' . $event->type);
                break;
        }

        // Always return success
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
        // Validate the request
        $request->validate([
            'attempt_id' => 'required|integer|exists:attempts,id',
        ]);
    
        try {
            // Retrieve the attempt with specific columns
            $attempt = DB::table('attempts')
                ->select('status', 'booking_id', 'expiration', 'tourradar_res', 'duffel_res', 'passengers')
                ->where('id', $request->attempt_id)
                ->first();
    
            Log::info('Attempt retrieved:', ['attempt' => $attempt]);
    
            if ($attempt && $attempt->tourradar_res && $attempt->duffel_res) {
                $tourradar_res = json_decode($attempt->tourradar_res, true);    
                $adultsNumber = $childrenNumber = 0;
                $totalPriceAdults = $totalPriceChildren = 0;
                $adultCategoryIds = $childCategoryIds = [];
    
                if (!empty($tourradar_res['passengers']) && is_array($tourradar_res['passengers'])) {
                    $adults = collect($tourradar_res['passengers'])->filter(fn($p) => isset($p['price_category']['title']) && $p['price_category']['title'] === 'Adult');
                    $children = collect($tourradar_res['passengers'])->filter(fn($p) => isset($p['price_category']['title']) && $p['price_category']['title'] === 'Child');
    
                    $adultsNumber = $adults->count();
                    $childrenNumber = $children->count();
    
                    if ($adults->isNotEmpty()) {
                        $adultCategoryIds = $adults->pluck('price_category.id')->all();
                    }
    
                    if ($children->isNotEmpty()) {
                        $childCategoryIds = $children->pluck('price_category.id')->all();
                    }
    
                    Log::info('Passenger count:', [
                        'adultsNumber' => $adultsNumber,
                        'childrenNumber' => $childrenNumber,
                    ]);
                } else {
                    Log::warning('No passengers found in tourradar_res.');
                }
    
                if (!empty($tourradar_res['accommodations']) && is_array($tourradar_res['accommodations'])) {
                    foreach ($tourradar_res['accommodations'] as $acc) {
                        if (!empty($acc['prices'])) {
                            foreach ($acc['prices'] as $price) {
                                if (in_array($price['price_category_id'], $adultCategoryIds)) {
                                    $totalPriceAdults += $price['price_per_pax'];
                                }
                                if (in_array($price['price_category_id'], $childCategoryIds)) {
                                    $totalPriceChildren += $price['price_per_pax'];
                                }
                            }
                        }
                    }
                } else {
                    Log::warning('No accommodations found in tourradar_res.');
                }
    
                $tourradar_res['adultsNumber'] = $adultsNumber;
                $tourradar_res['childrenNumber'] = $childrenNumber;
                $tourradar_res['totalPriceAdults'] = $totalPriceAdults;
                $tourradar_res['totalPriceChildren'] = $totalPriceChildren;
    
                $duffel_res = json_decode($attempt->duffel_res, true);
                Log::info('Decoded duffel_res:', ['duffel_res' => $duffel_res]);
    
                $duffelAdultsNumber = $duffelChildrenNumber = 0;
                if (isset($duffel_res['data']['passengers']) && is_array($duffel_res['data']['passengers'])) {
                    $duffelPassengers = collect($duffel_res['data']['passengers']);
    
                    $duffelAdultsNumber = $duffelPassengers->filter(fn($p) => isset($p['type']) && $p['type'] === 'adult')->count();
                    $duffelChildrenNumber = $duffelPassengers->filter(fn($p) => isset($p['type']) && $p['type'] === 'child')->count();
                } else {
                    Log::warning('No passengers found in duffel_res.');
                }
    
                $duffel_res['duffelAdultsNumber'] = $duffelAdultsNumber;
                $duffel_res['duffelChildrenNumber'] = $duffelChildrenNumber;
    
                return response()->json([
                    'status' => $attempt->status ?? 'pending',
                    'booking_id' => $attempt->booking_id ?? null,
                    'expiration' => $attempt->expiration ?? null,
                    'tourradar_res' => $tourradar_res,
                    'duffel_res' => $duffel_res,
                    'passengers' => json_decode($attempt->passengers, true),
                ]);
            }else {
                return response()->json([
                    'status' => $attempt->status ?? 'pending',
                    'reason' => "missing tourradar res or duffel res"
                ]);
            }
    
            // Return pending status if booking_id is not set
            return response()->json(['status' => 'pending'], 200);
        } catch (\Exception $e) {
            Log::error('Exception in checkBookingStatus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => 'An error occurred while checking booking status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
}

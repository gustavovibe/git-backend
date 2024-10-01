<?php

namespace App\Http\Controllers;

use App\Models\FlightTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\Traveler;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderTraveler;
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PackageController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
      /*   return $request->all(); */
        $stripeSecret = config('services.stripe.secret');

        $urlAppFront = config('services.stripe.urlAppFront');

        Stripe::setApiKey($stripeSecret);

        $RequestFlight = $request->input('flight');

        $RequestTour = $request->input('tour');

        $order = $this->bookPackage($RequestTour, $RequestFlight);

        if($order[0]==1){
            return response()->json(['success'=>false,'message'=>$order[1]]);
            /* return ApiResponse::error($order[1]); */
        }
        $order=$order[1];
        $tour_id = (int)$RequestTour['tour_id'];

        $tour = Tour::find($tour_id);

        $amount = $request->price_total;

        $url = $request->url;

        $parsedUrl = parse_url($url);

        parse_str($parsedUrl['query'], $queryParams);

        $newUrl = $urlAppFront . '/confirmation?' . http_build_query($queryParams) . '&order_id=' . $order->booking_id;

        $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount, $newUrl);

        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 500);
        }

        return response()->json(['url' => $response['url'], 'order' => $order]);

    }

    public function test(Request $request)
    {

        $tourBody = $request->input('tour');
        $tourResponse = TourRadarController::createNewBooking($tourBody);

        $flightBody = $request->input('flight');
        $flightResponse = DuffelApiController::createNewBooking($flightBody);

        return [
            'flight' => $flightResponse,
            'tour' => $tourResponse
        ];
    }

    private function createCheckoutSessionInternal($productName, $productDescription, $amount, $url)
    {

        try {
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
                'mode' => 'payment',
                'success_url' => $url,
                'cancel_url' => 'http://localhost:3000/book',
                'payment_method_options' => [
                    'card' => [
                        'setup_future_usage' => 'off_session',
                    ],
                ],
            ]);

            return ['url' => $session->url];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    public function bookPackage($tour, $flight)
    {

        $tourBody = $tour;
        $tourResponse = TourRadarController::createNewBooking($tourBody);
        if(isset($tourResponse['error']) && $tourResponse['error']){
            return [1, 'Tour radar:'.$tourResponse['message']] ;
        }
        $flightBody = $flight;
        $flightResponse = DuffelApiController::createNewBooking($flightBody);

        if(isset($flightResponse['errors']) && $flightResponse['errors']){
            return [1, 'Flight:'. $flightResponse['errors'][0]['message']] ;
        }
        $passengers = $tourResponse['passengers'];


        $firstIteration = true;

        $mainPassenger = "";

        $mainPassengerCountry = "";

        $mainPassengerAge = 0;

        $groupSize = count($tourBody['passengers']);
        $traveler_id=0;
        foreach ($passengers as $passenger) {
            if ($firstIteration) {

                $mainPassenger = $passenger['fields']['title']=='Mr.'?'male':'female';

                $mainPassengerCountry = $passenger['fields']['place_of_issue'];

                $user = User::updateOrCreate(
                    ['email' => $passenger['fields']['email']],
                    ['name' => $passenger['fields']['first_name'] . " " . $passenger['fields']['last_name'],
                        'password' => Hash::make('password123'),
                        'profile_id' => 2,
                        'phone' => $passenger['fields']['phone_number'],
                        'country' => $passenger['fields']['place_of_issue'],
                        'role' => 'role',
                        'active' => 1,
                        'suscribed' => 1,
                        'hear' => "without comment",]);

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

/*
        foreach ($passengers as $passenger) {
            if ($passenger['fields']['email'] == $user->email) {
                continue;
            }
            $traveler = Traveler::create([
                'title' => $passenger['fields']['title'],
                'gender' => $passenger['fields']['gender'],
                'name' => $passenger['fields']['first_name'],
                'last' => $passenger['fields']['last_name'],
                'birth' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth'])->format('Y-m-d'),
                'passport' => intval($passenger['fields']['passport_number']),
                'place' => $passenger['fields']['place_of_issue'],
                'issue' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['issue_date'])->format('Y-m-d'),
                'expire' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['expiration_date'])->format('Y-m-d'),
                'mail' => $passenger['fields']['email'],
                'phone' => $passenger['fields']['phone_number'],
                'address' => isset($passengers[0]['fields']['address'])? $passengers[0]['fields']['address']:'n/a',
                'country' => $passengers[0]['fields']['place_of_issue'],
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $order->travelers()->attach($traveler->traveler_id);
        } */
        return [0, $order];
    }

    public function createBaggageCheckoutSession(Request $r){
        try{
            $stripeSecret = config('services.stripe.secret');
            $urlAppFront = config('services.stripe.urlAppFront');

            Stripe::setApiKey($stripeSecret);
            $baggageType = $r->baggage_type;
            $baggageQuantity = $r->input('quantity', 1);
            $amount = $r->price * 100;


             $newUrl = $urlAppFront . "/my-trips/order?stripe_pay=true";
             /*  $newUrl =  "http://localhost:3000/my-trips/order?stripe_pay=true"; */
           /*    return $newUrl; */
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

public function getOrderDetails($order_id){
$url = 'https://api.duffel.com/air/orders/'.$order_id;
$response = Http::withHeaders($this->getDuffelHeaders())->get($url);
return $response->json();
}

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

        $url = "https://api.duffel.com/air/orders/{$r->order_id}";
        $response = Http::withHeaders($this->getDuffelHeaders())->post($url, $body);

        if ($response->failed()) {
            return response()->json(['status'=>false,'error' => $response->json()]);
        }

        return  response()->json(['status'=>true,'response'=>$response->json()]);
    }


}

public function handleStripeWebhook(Request $request)
{
    $stripeSecret = config('services.stripe.secret');
    $endpointSecret = 'whsec_0a3df2c66784e65c3a762066ff57b4f8784b7bdeb28ac6088375b4c431045b5b';

    Stripe::setApiKey($stripeSecret);

    $payload = @file_get_contents('php://input');
    $sigHeader = $request->header('Stripe-Signature');

    try {
        $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    } catch (SignatureVerificationException $e) {
        return response()->json(['status' => false, 'error' => 'Invalid signature'], 400);
    }

    if ($event->type == 'checkout.session.completed') {
        $session = $event->data->object;

        // Obtener los metadatos
        $order_id = $session->metadata->order_id;
        $passenger_id = $session->metadata->passenger_id;
        $checked = $session->metadata->checked;
        $this->updateDuffelOrder(new Request([
            'order_id' => $order_id,
            'passenger_id' => $passenger_id,
            'checked' =>  $checked,
        ]));
        return response()->json(['status' => true,'response'=>'entro a contenido de acciones']);
    }

    return response()->json(['status' => false, 'error' => 'Unhandled event type'], 400);
    }
}




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
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PackageController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $stripeSecret = config('services.stripe.secret');

        $urlAppFront = config('services.stripe.urlAppFront');

        Stripe::setApiKey($stripeSecret);


        $RequestFlight = $request->input('flight');

        $RequestTour = $request->input('tour');

        $order = $this->bookPackage($RequestTour, $RequestFlight);

        $tour_id = (int)$RequestTour['tour_id'];

        $tour = Tour::find($tour_id);

        $amount = $tour->price_total * 100;

        $url = $request->url;

        $parsedUrl = parse_url($url);

        parse_str($parsedUrl['query'], $queryParams);

        $newUrl = $urlAppFront . '/confirmation?' . http_build_query($queryParams) . '&order_id=' . $order->id;

        $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount, $newUrl);

        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 500);
        }

        return response()->json(['url' => $response['url'], 'order' => $order]);

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

        $flightBody = $flight;
        $flightResponse = DuffelApiController::createNewBooking($flightBody);

        $passengers = $tourBody['passengers'];

        $firstIteration = true;

        $mainPassenger = "";

        $mainPassengerCountry = "";

        $mainPassengerAge = 0;

        $groupSize = count($tourBody['passengers']);

        foreach ($passengers as $passenger) {
            if ($firstIteration) {
                $mainPassenger = $passenger['fields']['gender'];
                $mainPassengerCountry = $passenger['fields']['country'];

                $user = User::updateOrCreate(
                    ['email' => $passenger['fields']['email']],
                    ['name' => $passenger['fields']['first_name'] . " " . $passenger['fields']['last_name'],
                        'password' => Hash::make('password123'),
                        'profile_id' => 2,
                        'phone' => $passenger['fields']['phone_number'],
                        'country' => $passenger['fields']['country'],
                        'role' => 'role',
                        'active' => 1,
                        'suscribed' => 1,
                        'hear' => "without comment",]);

                $dob = Carbon::createFromFormat('d/m/Y', $passenger['fields']['date_of_birth']);


                $today = Carbon::now();

                $mainPassengerAge = $dob->diffInYears($today);

                $firstIteration = false;
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
                'expire' => Carbon::createFromFormat('d/m/Y', $passenger['fields']['expiration_date'])->format('Y-m-d'), 'mail' => $passenger['fields']['email'],
                'phone' => $passenger['fields']['phone_number'], 'address' => $passenger['fields']['address'],
                'country' => $passenger['fields']['country'], 'lead' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
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

        $orderData = [
            'departure' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['departing_at'])->format('Y-m-d'),
            'start' => $tourResponse['departure_date'],
            'arrival' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['arriving_at'])->format('Y-m-d'),
            'end' => $tourResponse['return_date'],
            'duration' => convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),
            'tour_length' => $adventureDuration,
            'tour_name' => $tourResponse['tour']['tour_name'],
            'tour_id' => $tourResponse['tour']['tour_id'],
            'operator' => $tourResponse['tour']['operator']['id'],
            'start_city' => $flightResponse['data']['slices'][0]['origin']['city_name'],
            'end_city' => $flightResponse['data']['slices'][0]['destination']['city_name'],
            'booking_status' => $tourResponse['status'],
            'tourradar_id' => $tourResponse['id'],
            'tourradar_status' => $tourResponse['status'],
            'tourradar_reason' => $tourResponse['status_reason'],
            'tourradar_text' => $tourResponse['status_reason_text'],
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
            'discounted' => $tourResponse['promotions'][0]['prices'][0]['price_per_pax'],
            'promo' => $tourResponse['promotions'][0]['id'],
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

        if ($order && $order->id) {
            $order->flightTour()->create([
                'flight' => $flightResponse,
                'tour' => $tourResponse,
            ]);
        } else {
            throw new \Exception('Order could not be created.');
        }

        return $order;
    }
}




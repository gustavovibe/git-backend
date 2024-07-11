<?php

namespace App\Http\Controllers;

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

        Stripe::setApiKey($stripeSecret);

        $RequestFlight = $request->input('flight');

        $RequestTour = $request->input('tour');

        $this->bookPackage($RequestTour, $RequestFlight);

        $tour_id = (int)$RequestTour['tour_id'];

        $tour = Tour::find($tour_id);

        $amount = $tour->price_total * 100;

        $response = $this->createCheckoutSessionInternal($tour->tour_name, $tour->description, $amount);

        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 500);
        }

        return response()->json(['url' => $response['url']]);
    }

    private function createCheckoutSessionInternal($productName, $productDescription, $amount)
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
                'success_url' => 'http://localhost:3000/book',
                'cancel_url' => 'http://localhost:3000/cancel',
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

        foreach ($passengers as $passenger) {
            if ($firstIteration) {
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

        $orderData = [
            'departure' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['departing_at'])->format('Y-m-d'),
            'start' => $tourResponse['departure_date'],
            'arrival' => Carbon::parse($flightResponse['data']['slices'][0]['segments'][0]['arriving_at'])->format('Y-m-d'),
            'end' => $tourResponse['return_date'],
            'duration' => convertDurationToMinutes($flightResponse['data']['slices'][0]['duration']),  // Asegúrate de llamar correctamente a este método
            'tour_length' => $tourResponse['tour']['tour_length_days'],
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
            'paid' => $tourResponse['total_value'],
            'p_flight' => $flightResponse['data']['total_amount'],
            'p_tour' => $tourResponse['total_value'],
            'discounted' => $tourResponse['promotions'][0]['prices'][0]['price_per_pax'],
            'promo' => $tourResponse['promotions'][0]['id'],
            'user_id' => $user->id
        ];

        $order = Order::create($orderData);

        return response()->json([
            "tourResponse" => $tourResponse,
            "flightResponse" => $flightResponse
        ]);
    }
}




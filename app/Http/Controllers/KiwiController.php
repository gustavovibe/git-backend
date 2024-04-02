<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class KiwiController extends Controller
{
    public static function searchApi($params)
    {
        $apiKey = "QGWd943iYcYa581oo0nm_m8Kl-BOL0an"; // Move to .env file
        $url = 'https://api.tequila.kiwi.com/v2/search';

        $headers = [
            'accept' => 'application/json',
            'apikey' => $apiKey,
        ];

        $url = $url . '?' . http_build_query($params);

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function checkFlightsApi($params)
    {
        $apiKey = "QGWd943iYcYa581oo0nm_m8Kl-BOL0an"; // Move to .env file
        $url = 'https://api.tequila.kiwi.com/v2/booking/check_flights';

        $headers = [
            'accept' => 'application/json',
            'apikey' => $apiKey,
        ];

        $url = $url . '?' . http_build_query($params);

        try {
            $response = Http::withHeaders($headers)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function saveBookingApi($params = null, $body)
    {
        $apiKey = "QGWd943iYcYa581oo0nm_m8Kl-BOL0an"; // Move to .env file
        $url = 'https://api.tequila.kiwi.com/v2/booking/save_booking';

        $headers = [
            'accept' => 'application/json',
            'apikey' => $apiKey,
        ];

        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        try {
            $response = Http::withHeaders($headers)->post($url, $body);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function confirmPaymentApi($body)
    {
        $apiKey = "QGWd943iYcYa581oo0nm_m8Kl-BOL0an"; // Move to .env file
        $url = 'https://api.tequila.kiwi.com/v2/booking/confirm_payment';

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'apikey' => $apiKey,
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $body);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

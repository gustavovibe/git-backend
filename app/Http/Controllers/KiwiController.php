<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class KiwiController extends Controller
{
    protected static $API_KEY = "QGWd943iYcYa581oo0nm_m8Kl-BOL0an";

    /**
     * search Api.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $params Params
     * @return array
     */
    public static function searchApi($params)
    {
        $url = 'https://api.tequila.kiwi.com/v2/search';

        $headers = [
            'accept' => 'application/json',
            'apikey' => self::$API_KEY,
        ];

        $url = $url . '?' . http_build_query($params);

        $response = Http::withHeaders($headers)->get($url);
        return $response->json();
    }

    /**
     * checkFlights Api.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $params Params
     * @return array
     */
    public static function checkFlightsApi($params)
    {
        $url = 'https://api.tequila.kiwi.com/v2/booking/check_flights';

        $headers = [
            'accept' => 'application/json',
            'apikey' => self::$API_KEY,
        ];

        $url = $url . '?' . http_build_query($params);

        $response = Http::withHeaders($headers)->get($url);
        return $response->json();
    }

    /**
     * saveBooking Api.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $params Params
     * @param array $body Body
     * @return array
     */
    public static function saveBookingApi($params = null, $body)
    {
        $url = 'https://api.tequila.kiwi.com/v2/booking/save_booking';

        $headers = [
            'accept' => 'application/json',
            'apikey' => self::$API_KEY,
        ];

        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        $response = Http::withHeaders($headers)->post($url, $body);
        return $response->json();
    }

    /**
     * confirmPayment Api.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $body Body
     * @return array
     */
    public static function confirmPaymentApi($body)
    {
        $url = 'https://api.tequila.kiwi.com/v2/booking/confirm_payment';

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'apikey' => self::$API_KEY,
        ];

        $response = Http::withHeaders($headers)->post($url, $body);
        return $response->json();
    }

    /**
     * confirmPaymentZooz Api.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param array $body Body
     * @return array
     */
    public static function confirmPaymentZoozApi($body)
    {
        $url = 'https://api.tequila.kiwi.com/v2/booking/confirm_payment_zooz';

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'apikey' => self::$API_KEY,
        ];

        $response = Http::withHeaders($headers)->post($url, $body);
        return $response->json();
    }
}

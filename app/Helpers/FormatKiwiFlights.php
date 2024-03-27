<?php

namespace App\Helpers;

class FormatKiwiFlights
{

    public static function formatKiwiFlights($apiResponse)
    {
        $response = $apiResponse;
        if (array_key_exists('data', $response)) {
            $response['data'] = self::formatDataAttribute($response['data']);
        }
        return $response;
    }

    private static function formatDataAttribute($apiData)
    {
        return array_map(function ($flight) {
            $modifiedFlight = $flight;
            if (array_key_exists('nightsInDest', $flight)) {
                unset($modifiedFlight['nightsInDest']);
            }
            return $modifiedFlight;
        }, $apiData);
    }
}

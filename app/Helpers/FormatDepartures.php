<?php

namespace App\Helpers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TourRadarController;

class FormatDepartures
{

    public static function formatDeparturesResponse($departures)
    {
        $response = [];
        foreach ($departures as $departure) {
            $departure['prices'] = self::formatDeparturePrices($departure['prices']);
            $departure['guide_languages'] = self::getGuideLanguagesForDeparture($departure);
            unset($departure['links']);
            array_push($response, $departure);
        }
        return $response;
    }

    public function formatDeparturePrices($prices)
    {
        $response = [];
        $response['based_on'] = $prices['based_on'];
        $response['price_total'] = $prices['price_total'];
        $response['mandatory_addons'] = [];
        $response['mandatory_addons'] = $prices['mandatory_addons'];
        return $response;
    }

    public function getGuideLanguagesForDeparture($departure)
    {
        $response = [];
        $taxonomy_languages = TourRadarController::getTaxonomyLanguages();
        foreach ($departure['guide_languages'] as $languageId) {
            foreach ($taxonomy_languages as $language) {
                if ($language['id'] === $languageId) {
                    array_push($response, $language);
                    break;
                }
            }
        }
        return $response;
    }

}

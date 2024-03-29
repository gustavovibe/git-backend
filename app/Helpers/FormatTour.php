<?php

namespace App\Helpers;

use App\Http\Controllers\TourRadarController;

class FormatTour
{
    public static function formatTourData($tour)
    {
        // dd($tour);
        $formatedTour = [];
        $formatedTour['tour_id'] = $tour['tour_id'];
        $formatedTour['tour_name'] = $tour['tour_name'];
        $formatedTour['overview'] = $tour['description'];
        $formatedTour['ratings'] = self::getRatings($tour);
        $formatedTour['reviews_count'] = $tour['reviews_count'];
        $formatedTour['tour_length_days'] = $tour['tour_length_days'];
        $formatedTour['max_group_size'] = $tour['max_group_size'];
        $formatedTour['images'] = self::getFormattedImages($tour);
        $formatedTour['map'] = self::getMapImage($tour);
        $formatedTour['guiding_method'] = self::getGuidingMethod($tour);
        $formatedTour['tour_type'] = self::getTourType($tour);
        $formatedTour['tour_types'] = $tour['tour_types'];
        $formatedTour['age_range_formatted'] = self::getAgeRange($tour);
        $formatedTour['age_range'] = $tour['age_range'];
        $formatedTour['guide_languages'] = self::getGuideLanguagesForTour($tour);
        $formatedTour['start_city'] = $tour['start_city'];
        $formatedTour['end_city'] = $tour['end_city'];
        $formatedTour['destinations'] = $tour['destinations'];
        $formatedTour['prices'] = self::getFormattedPrice($tour);
        $formatedTour['itinerary'] = $tour['itinerary'];
        $formatedTour['services'] = self::getServices($tour);
        $formatedTour['operator'] = $tour['operator'];

        return $formatedTour;
    }

    private function getRatings($tour)
    {
        if (isset($tour['ratings']['overall'])) {
            return $tour['ratings']['overall'];
        } else {
            return $tour['ratings']['operator'];
        }
    }

    private function getFormattedImages($tour)
    {
        $images = [];
        foreach ($tour['images'] as $image) {
            if ($image['type'] === "image") {
                array_push($images, $image['url']);
            }
        }
        return $images;
    }

    private function getMapImage($tour)
    {
        foreach ($tour['images'] as $image) {
            if ($image['type'] === "map") {
                return $image['url'];
            }
        }
        return null;
    }

    private function getGuidingMethod($tour)
    {
        $formatted = [];
        foreach ($tour['tour_types'] as $tourType) {
            if ($tourType['group_id'] === 2) {
                array_push($formatted, $tourType);
            }
        }
        return $formatted;
    }

    private function getTourType($tour)
    {
        $formatted = [];
        foreach ($tour['tour_types'] as $tourType) {
            if ($tourType['group_id'] === 1) {
                array_push($formatted, $tourType);
            }
        }
        return $formatted;
    }

    private function getAgeRange($tour)
    {
        $min = $tour['age_range']['strict']['min_age'];
        $max = $tour['age_range']['strict']['max_age'];
        return "{$min}-{$max}";
    }

    private function getGuideLanguagesForTour($tour)
    {
        $response = [];
        $taxonomy_languages = TourRadarController::getTaxonomyLanguages();
        foreach ($tour['guide_languages'] as $languageId) {
            foreach ($taxonomy_languages as $language) {
                if ($language['id'] === $languageId) {
                    array_push($response, $language);
                    break;
                }
            }
        }
        return $response;
    }

    private function getFormattedPrice($tour)
    {
        $response = [];
        $response['based_on'] = $tour['prices']['based_on'];
        $response['price_total'] = $tour['prices']['price_total'];
        $response['mandatory_addons'] = [];
        $response['mandatory_addons'] = $tour['prices']['mandatory_addons'];
        return $response;
    }


    private function getServices($tour)
    {
        $included = [];
        $excluded = [];
        foreach ($tour['services'] as $serviceName => $serviceDetails) {
            if (count($serviceDetails) === 0) {
                continue;
            }
            foreach ($serviceDetails as $service) {
                if ($service['is_included']) {
                    $included[$serviceName] = [];
                    array_push($included[$serviceName], $service);
                } else {
                    $excluded[$serviceName] = [];
                    array_push($excluded[$serviceName], $service);
                }
            }
        }
        $response = [];
        $response['included'] = $included;
        $response['excluded'] = $excluded;
        return $response;
    }
}

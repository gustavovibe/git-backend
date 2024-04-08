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
        // New format:
        $formatedTour['tourId'] = $tour['tour_id'];
        $formatedTour['tourName'] = $tour['tour_name'];
        $formatedTour['reviewsCount'] = $tour['reviews_count'];
        $formatedTour['tourLengthDays'] = $tour['tour_length_days'];
        $formatedTour['maxGroupSize'] = $tour['max_group_size'];
        $formatedTour['guidingMethod'] = self::getGuidingMethodName($tour);
        $formatedTour['tourType'] = self::getTourType($tour);
        $formatedTour['tourTypes'] = $tour['tour_types'];
        $formatedTour['ageRangeFormatted'] = self::getAgeRange($tour);
        $formatedTour['ageRange'] = $tour['age_range'];
        $formatedTour['guideLanguages'] = self::getGuideLanguagesForTour($tour);
        $formatedTour['startCity'] = $tour['start_city'];
        $formatedTour['endCity'] = $tour['end_city'];
        $formatedTour['groupType'] = self::getGroupType($tour);
        $formatedTour['accommodationDesc'] = self::getAccommodationDesc($formatedTour);
        $formatedTour['transportDesc'] = self::getTransportDesc($formatedTour);
        $formatedTour['othersDesc'] = self::getOthersDesc($formatedTour);
        $formatedTour['mealsDesc'] = self::getMealsDesc($formatedTour);
        $formatedTour['guideDesc'] = self::getGuideDesc($formatedTour);
        $formatedTour['ethersDesc'] = self::getEthersDesc($formatedTour);
        $formatedTour['flightsDesc'] = self::getFlightsDesc($formatedTour);
        $formatedTour['optionalDesc'] = self::getOptionalDesc($formatedTour);
        $formatedTour['insuranceDesc'] = self::getInsuranceDesc($formatedTour);
        $formatedTour['lastImage'] = self::getMapImage($tour);

        return $formatedTour;
    }

    private function getInsuranceDesc($formatedTour)
    {
        return $formatedTour['services']['excluded']['insurance'][0]['description'] ?? null;
    }

    private function getOptionalDesc($formatedTour)
    {
        return $formatedTour['services']['excluded']['optional'][0]['description'] ?? null;
    }

    private function getFlightsDesc($formatedTour)
    {
        return $formatedTour['services']['excluded']['flights'][0]['description'] ?? null;
    }

    private function getEthersDesc($formatedTour)
    {
        return $formatedTour['services']['excluded']['others'][0]['description'] ?? null;
    }

    private function getGuideDesc($formatedTour)
    {
        return $formatedTour['services']['included']['guide'][0]['description'] ?? null;
    }

    private function getMealsDesc($formatedTour)
    {
        return $formatedTour['services']['included']['meals'][0]['description'] ?? null;
    }

    private function getOthersDesc($formatedTour)
    {
        return $formatedTour['services']['included']['others'][0]['description'] ?? null;
    }

    private function getTransportDesc($formatedTour)
    {
        return $formatedTour['services']['included']['transport'][0]['description'] ?? null;
    }

    private function getAccommodationDesc($formatedTour)
    {
        return $formatedTour['services']['included']['accommodation'][0]['description'] ?? null;
    }

    private function getGroupType($tour)
    {
        $response = "Group";
        if ($tour['max_group_size'] <= 20) {
            return "Small Group";
        }
        return $response;
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

    private function getGuidingMethodName($tour)
    {
        foreach ($tour['tour_types'] as $tourType) {
            if ($tourType['group_id'] === 2) {
                return $tourType['type_name'];
            }
        }
        return "-";
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

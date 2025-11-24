<?php

namespace App\Helpers;

use App\Http\Controllers\TourRadarController;

use function PHPUnit\Framework\isEmpty;

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
        $formatedTour['priceCategories'] = $tour['priceCategories'];
        $formatedTour['bookingFields'] = $tour['bookingFields'];

        return $formatedTour;
    }

	private static function getInsuranceDesc($formatedTour)
	{
		return $formatedTour['services']['excluded']['insurance'][0]['description'] ?? 'Not provided';
	}

	private static function getOptionalDesc($formatedTour)
	{
		return $formatedTour['services']['excluded']['optional'][0]['description'] ?? 'Not provided';
	}

	private static function getFlightsDesc($formatedTour)
	{
		return $formatedTour['services']['excluded']['flights'][0]['description'] ?? 'Not provided';
	}

	private static function getEthersDesc($formatedTour)
	{
		return $formatedTour['services']['excluded']['others'][0]['description'] ?? 'Not provided';
	}

	private static function getGuideDesc($formatedTour)
	{
		return $formatedTour['services']['included']['guide'][0]['description'] ?? 'Not provided';
	}

	private static function getMealsDesc($formatedTour)
	{
		return $formatedTour['services']['included']['meals'][0]['description'] ?? 'Not provided';
	}

	private static function getOthersDesc($formatedTour)
	{
		return $formatedTour['services']['included']['others'][0]['description'] ?? 'Not provided';
	}

	private static function getTransportDesc($formatedTour)
	{
		return $formatedTour['services']['included']['transport'][0]['description'] ?? 'Not provided';
	}

	private static function getAccommodationDesc($formatedTour)
	{
		return $formatedTour['services']['included']['accommodation'][0]['description'] ?? 'Not provided';
	}

	private static function getGroupType($tour)
	{
		if ($tour['max_group_size'] <= 20) {
			return 'Small Group';
		}
		return 'Group';
	}

	public static function getRatings($tour)
	{
		return $tour['ratings']['overall'] ?? $tour['ratings']['operator'] ?? 'No ratings available';
	}

	private static function getFormattedImages($tour)
	{
		$images = [];
		foreach ($tour['images'] as $image) {
			if ($image['type'] === "image") {
				array_push($images, $image['url']);
			}
		}
		return $images;
	}

	private static function getMapImage($tour)
	{
		foreach ($tour['images'] as $image) {
			if ($image['type'] === "map") {
				return $image['url'];
			}
		}
		return null;
	}

	private static function getGuidingMethod($tour)
	{
		$filtered = array_filter($tour['tour_types'], function ($tourType) {
			return $tourType['group_id'] === 2;
		});

		// Return the first matching guiding method or null if none found
		return !empty($filtered) ? reset($filtered) : null;
	}

	private static function getGuidingMethodName($tour)
	{
		$method = array_filter($tour['tour_types'], function ($tourType) {
			return $tourType['group_id'] === 2;
		});
		return $method ? reset($method)['type_name'] : '-';
	}

	private static function getTourType($tour)
	{
		return array_filter($tour['tour_types'], function ($tourType) {
			return $tourType['group_id'] === 1;
		});
	}

	private static function getAgeRange($tour)
	{
		$min = $tour['age_range']['strict']['min_age'] ?? 'N/A';
		$max = $tour['age_range']['strict']['max_age'] ?? 'N/A';
		return "{$min}-{$max}";
	}

	private static function getGuideLanguagesForTour($tour)
	{
		$response = [];
		$taxonomy_languages = TourRadarController::getTaxonomyLanguages();
		if (!is_array($tour['guide_languages'])) {
			return $response;
		}
		foreach ($tour['guide_languages'] as $languageId) {
			$language = array_filter($taxonomy_languages, fn($lang) => $lang['id'] === $languageId);
			if (!empty($language)) {
				$response[] = reset($language);
			}
		}
		return $response;
	}

	private static function getFormattedPrice($tour)
	{
		return [
			'based_on' => $tour['prices']['based_on'] ?? 'N/A',
			'price_total' => $tour['prices']['price_total'] ?? 'N/A',
			'mandatory_addons' => $tour['prices']['mandatory_addons'] ?? [],
		];
	}

	private static function getServices($tour)
	{
		$included = [];
		$excluded = [];
		foreach ($tour['services'] as $serviceName => $serviceDetails) {
			foreach ($serviceDetails as $service) {
				if ($service['is_included']) {
					$included[$serviceName][] = $service;
				} else {
					$excluded[$serviceName][] = $service;
				}
			}
		}
		return [
			'included' => $included,
			'excluded' => $excluded,
		];
	}
}
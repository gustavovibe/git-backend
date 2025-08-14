<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Helpers\FormatTour;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\TourRadarController;
use App\Helpers\FormatDepartures;

class ProxyTourRadarController extends Controller
{

    /**
     * Show.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param int $id ID
     * @return array     
     */
    public function show($id)
    {
        $tour = TourRadarController::getTour($id);
        if (isset($tour['error'])) {
            return response()->json([
                'success' => false,
                'message' => $tour,
            ], 400);
        }
        $r = TourRadarController::getPriceCategoriesByTour($tour['tour_id']);
        $tour['priceCategories'] = $r['price_categories'];
        $tour['bookingFields'] = TourRadarController::getOperatorBookingFields($tour['operator']['id']);
        $tour = FormatTour::formatTourData($tour);
        return ApiResponse::success($tour);
    }

    public static function showTour($id){
        $tour = TourRadarController::getTour($id);
        if (isset($tour['error'])) {
            return response()->json([
                'success' => false,
                'message' => $tour,
            ], 400);
        }
        $r = TourRadarController::getPriceCategoriesByTour($tour['tour_id']);
        $tour['priceCategories'] = $r['price_categories'];
        $tour['bookingFields'] = TourRadarController::getOperatorBookingFields($tour['operator']['id']);
        $tour = FormatTour::formatTourData($tour);
        return $tour;
    }

    /**
     * Departures.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public static function departures(Request $request)
    {
        $rules = [
            'tourId' => 'required',
            'currency' => 'sometimes|in:AUD,CAD,EUR,GBP,NZD,USD',
            'page' => 'sometimes',
            'user_country' => 'nullable|integer|min:0',
            'date_range' => 'sometimes|regex:/^\d{8}-\d{8}$/',
        ];
        $messages = [
            'tourId.required' => 'El campo :attribute es obligatorio.',
            'currency.in' => 'El campo :attribute debe ser uno de los siguientes valores: AUD, CAD, EUR, GBP, NZD, USD.',
            'date_range.regex' => 'El campo :attribute debe tener el formato YYYYMMDD-YYYYMMDD.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getDeparturesByTour($request->all());
        if (isset($response['items'])) {
            $response['items'] = FormatDepartures::formatDeparturesResponse($response['items'] ?? []);
        }
        return ApiResponse::success($response);
    }

    /**
     * Departure.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function departure(Request $request)
    {
        
        $rules = [
            'tourId' => 'required',
            'departureId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getDeparture($request->all());
        return $response;
    }

    public function departuredb(Request $request)
    {
        
        $rules = [
            'departureId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getDeparturedb($request->all());
        return $response;
    }

    /**
     * Prices.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function prices(Request $request)
    {
        $rules = [
            'tourId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getPriceCategoriesByTour($request['tourId']);
        
        return ApiResponse::success($response);
    }

    /**
     * Booking fields.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */ 
    public function bookingFields(Request $request)
    {
        $rules = [
            'operatorId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }
        $response = TourRadarController::getOperatorBookingFields();
        return ApiResponse::success($response);
    }

    /**
     * Bookings list.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @return array     
     */
    public function bookingsList()
    {
        $response = TourRadarController::getBookingsList();
        return ApiResponse::success($response);
    }

    /**
     * Bookings store.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function bookingsStore(Request $request)
    {
        $rules = [
            'departure_id' => 'required',
            'user_country' => 'required',
            'currency' => 'required|in:AUD,CAD,EUR,GBP,NZD,USD',
            'email' => 'required',
            'passengers' => 'required|array',
            'passengers.*.pax_number' => 'required',
            'passengers.*.price_category_id' => 'required',
            'passengers.*.fields' => 'required|array',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::createNewBooking($request->all());
        return $response;
    }

    /**
     * Destinations.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */ 
    public function destinations(Request $request)
    {
        $rules = [
            'type' => 'required|in:continent,country,state,region,city,national-park,island,mountain,ocean,river,lake',
            'country_id' => 'sometimes',
            'limit' => 'sometimes',
            'page' => 'sometimes',
        ];
        $messages = [
            'type.in' => "El campo :attribute debe ser uno de los siguientes valores: 'continent' 'country' 'state' 'region' 'city' 'national-park' 'island' 'mountain' 'ocean' 'river' 'lake'",
            'type.required' => "El campo ':attribute' es obligatorio",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $response = TourRadarController::getTaxonomyDestinations($request->all());

        return $response;
    }
}

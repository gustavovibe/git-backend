<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;
use App\Helpers\FormatTour;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\TourRadarController;

class ProxyTourRadarController extends Controller
{
    public function show($id)
    {
        $token = TourRadarController::getAccessToken();
        $tour = TourRadarController::getTour($token, $id);
        return ApiResponse::success($tour);
    }

    public function departures(Request $request)
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

        $token = TourRadarController::getAccessToken();
        $response = TourRadarController::getDeparturesByTour($token, $request->all());
        return ApiResponse::success($response);
    }

    public function prices(Request $request)
    {
        $rules = [
            'tourId' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $token = TourRadarController::getAccessToken();
        $response = TourRadarController::getPriceCategoriesByTour($token, $request['tourId']);
        return ApiResponse::success($response);
    }
}

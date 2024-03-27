<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Helpers\FormatKiwiFlights;

class ProxyKiwiController extends Controller
{
    public function searchFlights(Request $request)
    {
        $rules = [
            'fly_from' => 'required',
            'fly_to' => 'required',
            'date_from' => 'required',
            'date_to' => 'required',
            'adults' => 'sometimes',
            'children' => 'sometimes',
            'infants' => 'sometimes',
            'curr' => 'sometimes|in:AUD,CAD,EUR,GBP,NZD,USD',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }
        $response = KiwiController::searchApi($request->all());
        $response = FormatKiwiFlights::formatKiwiFlights($response);
        return ApiResponse::success($response);
    }
}

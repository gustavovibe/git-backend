<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Helpers\FormatKiwiFlights;

class ProxyKiwiController extends Controller
{

    /**
     * Search flights.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
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

    /**
     * Check flights.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */ 
    public function checkFlights(Request $request)
    {
        $rules = [
            'booking_token' => 'required',
            'bnum' => 'required',
            'adults' => 'required',
            'children' => 'required',
            'infants' => 'required',
            'session_id' => 'sometimes',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }
        $response = KiwiController::checkFlightsApi($request->all());
        return ApiResponse::success($response);
    }

    /**
     * Save booking.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function saveBooking(Request $request)
    {
        $rules = [
            'visitor_uniqid' => 'sometimes',
            'health_declaration_checked' => 'sometimes',
            'lang' => 'sometimes',
            'passengers' => 'required',
            'currency' => 'sometimes',
            'locale' => 'sometimes',
            'booking_token' => 'required',
            'baggage' => 'sometimes',
            'session_id' => 'required',
            'payment_gateway' => 'sometimes',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }
        $params = [];
        if ($request->has('visitor_uniqid')) {
            $params['visitor_uniqid'] = $request->visitor_uniqid;
        }

        $body = $request->all();
        $response = KiwiController::saveBookingApi($params, $body);
        return ApiResponse::success($response);
    }

    /**
     * Confirm payment.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function confirmPayment(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'transaction_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $body = $request->all();
        $response = KiwiController::confirmPaymentApi($body);
        return ApiResponse::success($response);
    }

    /**
     * Confirm payment zooz.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array     
     */
    public function confirmPaymentZooz(Request $request)
    {
        $rules = [
            'payment_details' => 'required',
            'booking_id' => 'required',
            'order_id' => 'required',
            'paymentToken' => 'required',
            'paymentMethodToken' => 'required',
            'sandbox' => 'required',
            'language' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $body = $request->all();
        $response = KiwiController::confirmPaymentZoozApi($body);
        return ApiResponse::success($response);
    }
}

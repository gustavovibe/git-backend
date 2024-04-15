<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $responseData = [];

        if ($request->has('country')) {
            $countries = $this->extractArrayFromQueryParam($request->input('country'));
            $responseData['country'] = $this->filterByCountry($countries, $perPage);
        }

        if ($request->has('city')) {
            $cities = $this->extractArrayFromQueryParam($request->input('city'));
            $responseData['city'] = $this->filterByCity($cities, $perPage);
        }

        if ($request->has('natural_destination')) {
            $naturalDestinations = $this->extractArrayFromQueryParam($request->input('natural_destination'));
            $responseData['natural_destinations'] = $this->filterByNaturalDestination($naturalDestinations, $perPage);
        }

        if (empty($responseData)) {
            $tours = Tour::paginate($perPage);
            return ApiResponse::success($tours);
        }

        return ApiResponse::success($responseData);
    }

    protected function extractArrayFromQueryParam($param)
    {

        $param = trim($param, '[]');

        $values = explode(',', $param);

        return array_map('trim', $values);
    }

    protected function filterByCountry($countryId, $perPage)
    {
        $query = Tour::whereHas('countries', function ($query) use ($countryId) {
            $query->where('t_country_id', $countryId);
        })->with('cities', 'natural_destination')->paginate($perPage);

        return TourResource::collection($query->items());
    }

    protected function filterByCity($cityId, $perPage)
    {
        $query = Tour::whereHas('cities', function ($query) use ($cityId) {
            $query->where('t_city_id', $cityId);
        })->with('countries', 'natural_destination')->paginate($perPage);

        return TourResource::collection($query->items());
    }

    protected function filterByNaturalDestination($naturalId, $perPage)
    {
        $query = Tour::whereHas('natural_destination', function ($query) use ($naturalId) {
            $query->where('t_natural_id', $naturalId);
        })->with('cities', 'countries')->paginate($perPage);

        return TourResource::collection($query->items());
    }


    public function show($id)
    {
        $tour = Tour::with('cities', 'countries', 'natural_destination')->where('tour_id', $id)->first();

        if (!$tour) {
            return response()->json(['message' => 'Tour no encontrado'], 404);
        }

        return ApiResponse::success($tour);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::query();

        if ($request->has('country')) {
            $countries = $this->extractArrayFromQueryParam($request->input('country'));
            $query->orWhereHas('countries', function ($q) use ($countries) {
                $q->whereIn('t_country_id', $countries);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('city')) {
            $cities = $this->extractArrayFromQueryParam($request->input('city'));
            $query->orWhereHas('cities', function ($q) use ($cities) {
                $q->whereIn('t_city_id', $cities);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('natural_destination')) {
            $naturalDestinations = $this->extractArrayFromQueryParam($request->input('natural_destination'));
            $query->orWhereHas('natural_destination', function ($q) use ($naturalDestinations) {
                $q->whereIn('t_natural_id', $naturalDestinations);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('tour_type')) {
            $tourType = $this->extractArrayFromQueryParam($request->input('tour_type'));
            $query->orWhereHas('type', function ($q) use ($tourType) {
                $q->whereIn('tour_type_id', $tourType);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');
            if ($sortBy === 'price_total') {
                $query->orderBy('price_total', $sortOrder);
            }
        }

        if ($request->has('tour_ids')) {
            $tourIds = $this->extractArrayFromQueryParam($request->input('tour_ids'));
            $query->whereIn('tour_id', $tourIds);
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        $results = $query->get();

        return ApiResponse::success($results);
    }

    protected function extractArrayFromQueryParam($param)
    {
        $param = trim($param, '[]');
        $values = explode(',', $param);
        return array_map('trim', $values);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TourIdController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::query();

        if ($request->has('country')) {
            $countries = $this->extractArrayFromQueryParam($request->input('country'));
            $query->orWhereHas('countries', function ($q) use ($countries) {
                $q->whereIn('t_country_id', $countries);
            });
        }

        if ($request->has('city')) {
            $cities = $this->extractArrayFromQueryParam($request->input('city'));
            $query->orWhereHas('cities', function ($q) use ($cities) {
                $q->whereIn('t_city_id', $cities);
            });
        }

        if ($request->has('natural_destination')) {
            $naturalDestinations = $this->extractArrayFromQueryParam($request->input('natural_destination'));
            $query->orWhereHas('natural_destination', function ($q) use ($naturalDestinations) {
                $q->whereIn('t_natural_id', $naturalDestinations);
            });
        }

        if ($request->has('tour_type')) {
            $tourType = $this->extractArrayFromQueryParam($request->input('tour_type'));
            $query->orWhereHas('type', function ($q) use ($tourType) {
                $q->whereIn('tour_type_id', $tourType);
            });
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');

            $validSortFields = ['price_total', 'tour_length_days', 'reviews_count', 'ratings_overall'];

            if (in_array($sortBy, $validSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Filter by guaranteed departures
        $query->where('departures', 'guaranteed');
        
        // Get all matching tour IDs
        $tourIds = $query->pluck('tour_id'); // Assuming 'id' is the column name for tour_id

        // Count the total number of tour IDs
        $total = $tourIds->count();

        // Prepare the response
        $response = [
            'tour_ids' => $tourIds,
            'total' => $total
        ];

        // Return the response using the ApiResponse helper
        return ApiResponse::success($response);
    }

    protected function extractArrayFromQueryParam($param)
    {
        $param = trim($param, '[]');
        $values = explode(',', $param);
        return array_map('trim', $values);
    }
}

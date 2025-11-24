<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Exception;

class TourIdController extends Controller
{

    /**
     * Display a listing of the resource.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function index(Request $request)
    {   
        try{
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

            if ($request->has('day_price')) {
                $dayPrice = $request->input('day_price');
                $query->whereRaw('price_total / tour_length_days <= ?', [$dayPrice]);
            }

            if ($request->has('sort_by') && $request->has('sort_order')) {
                $sortBy = $request->input('sort_by');
                $sortOrder = $request->input('sort_order');

                $validSortFields = ['price_total', 'tour_length_days', 'reviews_count', 'ratings_overall', 'price_day'];

                if (in_array($sortBy, $validSortFields)) {
                    if ($sortBy == 'price_day') {
                        $query->orderByRaw('price_total / tour_length_days ' . $sortOrder);
                    } else {
                        $query->orderBy($sortBy, $sortOrder);
                    }
                }
            }

            $query->whereIn('is_active', [1, 2]);

            // -- apply limit if provided --
            if ($request->has('limit')) {
                $limit = (int) $request->input('limit');
                // optional: enforce a max cap, e.g. 200
                //$limit = min($limit, 200);
                $query->limit($limit);
            }
    
            // -- fetch the IDs --
            $tourIds = $query->pluck('tour_id');
    
            // -- total returned (after limit) --
            $total = $tourIds->count();
    
            return ApiResponse::success([
                'tour_ids' => $tourIds,
                'total'    => $total,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'   => false,
                'response' => $e->getMessage(),
            ]);
        }
    }

    protected function extractArrayFromQueryParam($param)
    {
        $param = trim($param, '[]');
        $values = explode(',', $param);
        return array_map('trim', $values);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Resources\TourCityResource;
use App\Models\TourCity;

class TourCitiesController extends Controller
{
    /**
     * Display a listing of Tour Cities.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function index(Request $request)
    {
        $perPage = 10;

        $q = $request->input('q');

        $query = TourCity::query()->with('city', 'tour');

        if ($q) {
            $query = TourCity::where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $query = TourCity::paginate($perPage);
        }
        $responseData =  $query->toArray();

        $responseData['data'] = TourCityResource::collection($query->items());

        return ApiResponse::success($responseData);
    }
}

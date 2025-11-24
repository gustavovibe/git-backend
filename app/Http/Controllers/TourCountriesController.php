<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourCountryResource;
use App\Models\TourCountry;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;


class TourCountriesController extends Controller
{

    /**
     * Display a listing of Tour Countries.
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

        $query = TourCountry::query()->with('country', 'tour');

        if ($q) {
            $query = TourCountry::where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $query = TourCountry::paginate($perPage);
        }
        $responseData =  $query->toArray();

        $responseData['data'] = TourCountryResource::collection($query->items());

        return ApiResponse::success($responseData);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CitiesImport;
use App\Models\City;
use App\Http\Resources\CityResource;
use App\Helpers\ApiResponse;
use App\Models\Country;
use App\Models\NaturalDestination;
use App\Http\Resources\CountryResource;
use App\Http\Resources\NaturalDestinationResource;

class Citycontroller extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new CitiesImport, $file);
        } catch (\Exception $e) {

            return ApiResponse::success([], 'Import not successful', dd($e));
        }

        return ApiResponse::success([], 'Successful import');
    }

    public function index(Request $request)
    {
        $perPage = 10;

        $q = $request->input('q');

        if ($q) {
            $paginatedData = City::where('city_name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = City::paginate($perPage);
        }

        $responseData = $paginatedData->toArray();

        $responseData['data'] = CityResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    public function DestinatioCityCountryNaturalDestination(Request $request)
    {

        $perPage = 5;

        $q = $request->input('q');

        if ($q) {

            $country = Country::where('name', 'like', $q . '%')->paginate($perPage);
            $responseDataCountry = $country->toArray();
            $responseDataCountry['data'] = CountryResource::collection($country->items());

  


            $city = City::where('city_name', 'like', $q . '%')->paginate($perPage);
            $responseDataCity = $city->toArray();
            $responseDataCity['data'] = CityResource::collection($city->items());





            $natural = NaturalDestination::where('destination_name', 'like', $q . '%')->paginate($perPage);
            $responseDataNatural = $natural->toArray();
            $responseDataNatural['data'] = NaturalDestinationResource::collection($natural->items());


            $responseData = [
                'country' => $responseDataCountry,
                'city' => $responseDataCity,
                'natural_destinations' => $responseDataNatural
            ];

            return ApiResponse::success($responseData);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Filters\ToursFilters;
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
use Illuminate\Support\Facades\DB;

use Exception;

class Citycontroller extends Controller
{
    protected $list;
    protected $column;
    public function __construct(){
        $this->list=[
            1=>'t_country_id as id, name as name',
            2=>'t_city_id as id, city_name as name',
            3=>'t_natural_id as id, destination_name as name',
        ];
        $this->column=[
            1=>'name',
            2=>'city_name',
            3=>'destination_name',
        ];
    }
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

    public function destinations(Request $r){
        try{
            $destinations=ToursFilters::destinations($r);
            return response()->json(['status'=>true,'count'=>count($destinations),'response'=>$destinations]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    public function selectiontable(Request $r){
        try{
            $selection=$r->code==3?NaturalDestination::query():($r->code==2?City::query():Country::query());
            $selection->selectRaw($this->list[$r->code]);
            !$r->name?:$selection->where( $this->column[$r->code],'like',"%{$r->name}%");
            $selection=$selection->limit(15)->get();
            return response()->json(['status'=>true,'response'=>$selection]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ApiResponse;
use App\Http\Resources\CountryResource;
use App\Imports\CountryImport;
use App\Models\Country;

class CountryController extends Controller
{

    /**
     * Import countries.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new CountryImport, $file);
        } catch (\Exception $e) {

            return ApiResponse::success([], 'Import not successful');
        }

        return ApiResponse::success([], 'Successful import');
    }

    /**
     * Get all countries.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function index(Request $r)
    {
        $perPage = 10;

        $q = $r->q;

        $paginatedData = Country::query();

        !$q?:$paginatedData->where('name', 'like', $q . '%');

        $paginatedData = !$r->list? $paginatedData->paginate($perPage): $paginatedData->get();

        $responseData = $paginatedData->toArray();

        if(!$r->list){
            $responseData['data'] = CountryResource::collection($paginatedData->items());
        }

        return ApiResponse::success( $r->list? $paginatedData :$responseData);
    }

    /**
     * Get countries.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function getAllCountries(Request $request)
    {
        $countries = Country::all();

        return ApiResponse::success(CountryResource::collection($countries));
    }

    /**
     * Get countries with params.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function getCountries(Request $r)
    {
        $countries = Country::query();
        !$r->name?:$countries->where('name','like',"{$r->name}");
        !$r->id?:$countries->where('t_country_id',"{$r->country}");

        return ApiResponse::success($countries->get());
    }
}

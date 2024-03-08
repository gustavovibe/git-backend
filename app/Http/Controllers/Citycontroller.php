<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CitiesImport;
use App\Models\City;
use App\Http\Resources\CityResource;
use App\Helpers\ApiResponse;

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

            return ApiResponse::success([], 'Import not successful');
        }

        return ApiResponse::success([], 'Successful import');
    }

    public function index()
    {
        $perPage = 10;

        $paginatedData = City::paginate($perPage);

        $responseData = $paginatedData->toArray();

        $responseData['data'] = CityResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }
}

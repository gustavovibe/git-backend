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

    public function index(Request $request)
    {
        $perPage = 10;
        
        $q = $request->input('q');

        if ($q) {
            $paginatedData = Country::where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = Country::paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = CountryResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }
}

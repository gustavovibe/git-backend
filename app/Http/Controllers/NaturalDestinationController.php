<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ApiResponse;
use App\Http\Resources\NaturalDestinationResource;
use App\Imports\NaturalDestinationImport;
use App\Models\NaturalDestination;

class NaturalDestinationController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new NaturalDestinationImport, $file);
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
            $paginatedData = NaturalDestination::where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = NaturalDestination::paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = NaturalDestinationResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    public function show($id)
    {

        $natural = NaturalDestination::where('destination_id', '=', $id)->first();


        if (!$natural) {
            return response()->json(['message' => 'Natural destination not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $natural,
        ], 200);
    }

}

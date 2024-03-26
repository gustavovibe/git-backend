<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TourController extends Controller
{
    public function index(Request $request)
    {

        $perPage = 10;

        $q = $request->input('q');

        if ($q) {
            $paginatedData = Tour::with('cities', 'countries', 'natural_destination')->where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = Tour::with('cities', 'countries', 'natural_destination')->paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = TourResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }


    public function show($id)
    {
        $tour = Tour::with('cities', 'countries', 'natural_destination')->where('tour_id', $id)->first();

        if (!$tour) {
            return response()->json(['message' => 'Tour no encontrado'], 404);
        }

        return ApiResponse::success($tour);
    }
}

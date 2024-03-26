<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourNaturalDestinationResource;
use App\Models\TourNaturalDestination;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TourNaturalDestinationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;

        $q = $request->input('q');

        $query = TourNaturalDestination::query()->with('natural_destination', 'tour');

        if ($q) {
            $query = TourNaturalDestination::where('name', 'like', $q . '%')->paginate($perPage);
        } else {
            $query = TourNaturalDestination::paginate($perPage);
        }
        $responseData =  $query->toArray();

        $responseData['data'] = TourNaturalDestinationResource::collection($query->items());

        return ApiResponse::success($responseData);
    }
}

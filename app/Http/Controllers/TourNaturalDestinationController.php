<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourNaturalDestinationResource;
use App\Models\TourNaturalDestination;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Exception;

class TourNaturalDestinationController extends Controller
{
    public function index(Request $request)
    {
        try{
            $perPage = 10;

            $q = $request->input('q');

            $query = TourNaturalDestination::query()->with('natural_destination', 'tour');

            $query= $q?TourNaturalDestination::where('name', 'like', $q . '%')->paginate($perPage):TourNaturalDestination::paginate($perPage);
         /*    if ($q) {
                $query = TourNaturalDestination::where('name', 'like', $q . '%')->paginate($perPage);
            } else {
                $query = TourNaturalDestination::paginate($perPage);
            } */
            $responseData =  $query->toArray();

            $responseData['data'] = TourNaturalDestinationResource::collection($query->items());

            return ApiResponse::success($responseData);

        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }

    }
}

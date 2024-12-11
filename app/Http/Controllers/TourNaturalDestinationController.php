<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourNaturalDestinationResource;
use App\Models\TourNaturalDestination;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\NaturalDestination;
use App\Models\Type;
use Exception;

class TourNaturalDestinationController extends Controller
{

    /**
     * Display a listing of Tour Natural Destinations.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function index(Request $r)
    {
        try{

            $query = NaturalDestination::query();
            !$r->name?: $query->where('destination_name', 'like',"%$r->name%");
            $query= $query->get();
            return ApiResponse::success($query);

        }catch(Exception $e){
            return response()->json(['success'=>false,'response'=>$e->getMessage()]);
        }

    }

    /**
     * Type.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function Type(Request $r){
        try{
            $type = Type::query();
            !$r->name?: $type->where('tourtype_name', 'like',"%$r->name%");
            $type= $type->get();
            return ApiResponse::success($type);
        }catch(Exception $e){
            return response()->json(['success'=>false,'response'=>$e->getMessage()]);

        }
    }
}

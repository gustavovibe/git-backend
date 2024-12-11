<?php

namespace App\Http\Controllers;

use App\Filters\OperatorsFilters;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TourRadarController;
use App\Models\Operators;
use Exception;

class OperatorsController extends Controller
{

    /**
     * Display a listing of the resource.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function index(Request $r)
    {
        try{
            $operator = OperatorsFilters::OperatorsF($r);
            return response()->json(['status'=>true, 'response'=>$operator]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Get operators.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function operatorsList(Request $r){
        try{
            $operator = Operators::query();
            !$r->name?:$operator->where('name','like',"%$r->name%");
            !$r->operator_id?:$operator->where('operator_id',$r->operator_id);

             return response()->json(['success'=>true,'data'=>$operator->get()]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    /**
     * Import operators.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @return array
     */
    public function import(){
        $scope = 'com.tourradar.operators/read';
        $accessToken =TourRadarController::getAccessToken($scope) ;
        $url = "https://api.sandbox.b2b.tourradar.com/v1/operators";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        try {
            $response = Http::withHeaders($headers)->get($url);
            $response= $response->json();
            /* return $response; */
            array_map(function($res){
                $op=Operators::where('operator_id',$res['id'])->first();
                $operator=$op?$op:new Operators;
                $operator->fill([
                    'operator_id'=>$res['id'],
                    'name'=>$res['name'],
                ])->save();
              return $res;
            },$response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Get text.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function text(Request $r){
        try{
            $scope = 'com.tourradar.operators/read';
            $accessToken =TourRadarController::getAccessToken($scope) ;
            $url = "https://api.sandbox.b2b.tourradar.com/v1/operators/{$r->operator_id}";
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ];
            $response = Http::withHeaders($headers)->get($url);
            $response= $response->json();
            return response()->json(['status'=>true,'response'=>$response]);
        }catch(Error $e){
            return response()->json(['status'=>true, 'response' =>$e ]);
        }
    }
}

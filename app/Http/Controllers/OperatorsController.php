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
    public function index(Request $r)
    {
        try{
            $operator = OperatorsFilters::OperatorsF($r);
         /*    return $operator; */
            return response()->json(['status'=>true,'count'=>count($operator), 'response'=>$operator]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
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

<?php

namespace App\Http\Controllers;
use App\Models\User; 
use App\Models\GravitecSubscriber; 
use App\Helpers\ApiResponse;

use Illuminate\Http\Request;

class PushNotificationsController extends Controller
{
  public function registerGravitecSub(Request $request){

    $user_id = $request->post('user_id');
    $tour_id = $request->post('tour_id');
    $gravitec_data = $request->post('gravitec_data');

    if(isset($gravitec_data['regID'])){

      $get_subscriber = GravitecSubscriber::where('reg_id', $gravitec_data['regID'])->first();

      if(!$get_subscriber){

        try {
          
          $insert_data = [
            'reg_id' => $gravitec_data['regID'],
            'user_id' => $user_id,
            'alias' => '',
            'is_subscribed' => 1,
            'sub_data' => json_encode($gravitec_data)
          ];
          $new_gravitec_sub = GravitecSubscriber::create($insert_data);
          $get_subscriber = GravitecSubscriber::where('reg_id', $gravitec_data['regID'])->first();

          return ApiResponse::success($get_subscriber, 'Successful import');
        } catch (\Exception $e) {
          return ApiResponse::error($e->getMessage());
        }

      }else{
        return ApiResponse::success($get_subscriber, 'Current Sub');
      }

    }// end if(isset($gravitec_data['regID'])){

  }
}

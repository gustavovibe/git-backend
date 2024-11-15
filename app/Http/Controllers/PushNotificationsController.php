<?php

namespace App\Http\Controllers;
use App\Models\User; 
use App\Models\GravitecSubscriber; 
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

  public function sendPushNotification(Request $request){

    $get_subscriber = GravitecSubscriber::where('is_subscribed', 1)->get()->toArray();
    $data = [
        "send_date" => "",
        "ttl" => "",
        "display_time" => "",
        "is_transactional" => "",
        "payload" => [
          "message" => "New dates for Japan",
          "title" => "¡hello there!",
          "icon" => "https://push.gravitec.net/img/gravitecBig.jpg",
          "redirect_url" => "https://hopeful-nobel.74-208-189-166.plesk.page/"
        ]
      ];
    try {
      
      $api_secret = '9e0c99b90d7e3c5f72fabebc02870349';
      $api_key = 'e1eb502b026b42b32acb2017a8ee82a0';
      $url = 'https://uapi.gravitec.net/api/v3/push';
    
      $response = Http::withBasicAuth($api_key, $api_secret)
      ->accept('application/json')
      ->post($url, $data);

      return ApiResponse::success($response->json(), 'Current Subs');
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }

  }
}

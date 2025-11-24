<?php

namespace App\Http\Controllers;
use App\Models\User; 
use App\Models\GravitecSubscriber; 
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PushNotificationsController extends Controller
{

  /**
   * 
   * Register Device on Gravitec Platform: https://scandalous-ferry-f93.notion.site/9-Server-API-574e5b75ae9f4e1baaee67a2fbe441dd
   * 
   * Gets:
   * User Id (number)
   * Gravitec Data (array)
   *  - RegID (number) this is the device id gravitec provides from subscription
   */
  public function registerGravitecSub(Request $request){

    $user_id = $request->post('user_id');
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

  /**
   * 
   * Send Push Notification (Gravitec Platform)
   * Gets:
   * Push Type (string) define the place where is sent from
   *  - Wishlist
   *      Data (array) Wishlist item with info to send into notification
   *  - AbandonedCart
   *      Attempt Id (number) Attempt id from cart to get info and send it to notification
   * User Id (number)
   * 
   */
  public function sendPushNotification(Request $request){

    $push_type = $request->has('notification_type') && !empty($request->notification_type) ? $request->notification_type: '';
    if(empty($push_type)){
      return ApiResponse::error('Push notification type is missing');
    }

    $push_data = array();
    switch($push_type){

      case 'wishlist_update':
        //get wishlist update from ¿?
        $user_id = $request->has('user_id') ? $request->user_id : 0;
        if(empty($user_id)){
          return ApiResponse::error('User Id is missing');
        }

        $wishlist_items = $request->has('data') ? $request->data : [];
        if(empty($wishlist_items)){
          return ApiResponse::error('Data is missing');
        }

        $suscriber = GravitecSubscriber::where('user_id', $user_id)->first();
        $gravitec_token = $suscriber->reg_id;

        foreach($wishlist_items as $tour){

          $tour = $tour['tour']['data'];
          $push_icon =  !empty($tour['lastImage']) ? $tour['lastImage']: "https://push.gravitec.net/img/gravitecBig.jpg";
          $push_title = 'Good News on your Wishlist';
          $push_message = 'New prices for "'.$tour['tourName']. '" tour';
          $redirect_url = rtrim(config('frontend.url'), '/') . "/tour?tourId=" . $tour['tourId'];
          $push_data = [
            "send_date" => "",
            "ttl" => "",
            "display_time" => "",
            "is_transactional" => "",
            "payload" => [
              "message" => $push_message,
              "title" => $push_title,
              "icon" => $push_icon,
              "redirect_url" => $redirect_url
            ],
            "audience"=>[
              "tokens"=> [$gravitec_token]
            ]
          ];

          if(empty($push_data)){
            return ApiResponse::error('Push notification payload is missing');
          }
          
          try {
            
            $api_secret = '9e0c99b90d7e3c5f72fabebc02870349';
            $api_key = 'e1eb502b026b42b32acb2017a8ee82a0';
            $url = 'https://uapi.gravitec.net/api/v3/push';
          
            $response = Http::withBasicAuth($api_key, $api_secret)
                              ->accept('application/json')
                              ->post($url, $data);
      
            return ApiResponse::success($response->json(), 'Push notification sent');
          } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
          }

        }

      break;

      case 'abandoned_cart':

        $user_id = $request->has('user_id') ? $request->user_id : 0;
        if(empty($user_id)){
          return ApiResponse::error('User Id is missing');
        }

        $attempt_id = $request->has('attempt_id') ? $request->attempt_id : 0;
        $tour_id = $request->has('tour_id') ? $request->tour_id : 0;
        if(empty($attempt_id) && empty($tour_id)){
          return ApiResponse::error('Cart Id is missing');
        }
        
        $tour = [];
        $attempt = [];
        if(!empty($tour_id)){
          $tour = DB::table('tours')->where('tour_id', $tour_id)->first();
        }
        if(!empty($attempt_id)){
          $attempt = DB::table('attempts')->where('id', $attempt_id)->first();
        }
        $push_message = 'Click here to complete your reservation!';
        
        if(!empty($attempt)){
          // Process the stored data from the attempt
          $RequestTour = json_decode($attempt->tour, true);
          $passanger = 'Adventurer';
          if($RequestTour['passengers']['fields']){
            $passanger =  $RequestTour['passengers']['fields']['first_name']. ' '. $RequestTour['passengers']['fields']['last_name'];
          }
          $tour_name = $RequestTour['tour_name'];
          $push_message = !empty($passanger) ? 'Dear '.$passanger.' you forgot to complete your purchase for: '. $tour_name : 'Click here to complete your reservation!';
          $redirect_url = rtrim(config('frontend.url'), '/') . "/tour?tourId=" . $tour['tourId'];


        }elseif(!empty($tour)){
          
          $tour_name = $tour->tour_name;
          $push_message = 'Dear Traveler you forgot to complete your purchase for: '. $tour_name .'. Click here to complete your reservation!';
          $redirect_url = rtrim(config('frontend.url'), '/') . "/tour?tourId=" . $tour['tourId'];
          
        }else{
          return ApiResponse::error('Tour data not found');
        }

        $suscriber = GravitecSubscriber::where('user_id', $user_id)->first();
        $gravitec_token = $suscriber->reg_id;
        $push_icon =  !empty($RequestTour['thumbnail']) ? $RequestTour['thumbnail']: "https://push.gravitec.net/img/gravitecBig.jpg";
        $push_title = '¡Oops! Looks like you forgot to complete your reservation';
        
        $push_data = [
          "send_date" => "",
          "ttl" => "",
          "display_time" => "",
          "is_transactional" => "",
          "payload" => [
            "message" => $push_message,
            "title" => $push_title,
            "icon" => $push_icon,
            "redirect_url" => $redirect_url
          ],
          "audience"=>[
            "tokens"=> [$gravitec_token]
          ]
        ];

        if(empty($push_data)){
          return ApiResponse::error('Push notification payload is missing');
        }
        
        try {
          
          $api_secret = '9e0c99b90d7e3c5f72fabebc02870349';
          $api_key = 'e1eb502b026b42b32acb2017a8ee82a0';
          $url = 'https://uapi.gravitec.net/api/v3/push';
        
          $response = Http::withBasicAuth($api_key, $api_secret)
                            ->accept('application/json')
                            ->post($url, $data);
    
          return ApiResponse::success($response->json(), 'Push notification sent');
        } catch (\Exception $e) {
          ApiResponse::error($e->getMessage());
        }

      break;

      default:break;

    }

  }
}

<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\User; 
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class WishlistController extends Controller
{
    /**
     * Get User's Wishlist by User Id.
     * 
     * Gets:
     * User id (number)
     *
     */
    public function index(Request $request)
    {
        $user_id = $request->has('user_id') ? $request->post('user_id') : 0;
        if(!empty($user_id)){
            $traveler = Traveler::where('user_id', $user_id)->first();
            if($traveler->traveler_id){
                $wishlist = Wishlist::where('traveler_id', $traveler->traveler_id)->first();
                ApiResponse::success($wishlist, 'User Wishlist');
            }
        }
        $wishlists = Wishlist::all();
        return ApiResponse::success($wishlists, 'User Wishlist');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wishlist = Wishlist::findOrFail($id);
        return response()->json($wishlist);
    }


    /**
     * 
     * 
     * Adding tour to user's wishlist
     * 
     * Gets:
     * User id (number)
     * Tour id (number)
     *
     */
    public function store(Request $request){

        $tour_id = $request->has('tour_id') ? $request->post('tour_id') : 0;
        $user_id = $request->has('user_id') ? $request->post('user_id') : 0;

        if(empty($tour_id)){
            return ApiResponse::error('Tour ID is missing');
        }
        if(empty($user_id)){
            return ApiResponse::error('User ID is missing');
        }

        $traveler = Traveler::where('user_id', $user_id)->first();
        if (!$traveler) {
            return ApiResponse::error('Traveler not found');
        }

        $user = User::where('id', $user_id)->first();
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        try{

          $insert_data = [
              'traveler_id' => $traveler->traveler_id,
              'user_id' => $user_id,
              'wish_id' => 0,
              'tour_id' => $tour_id,
              'notes' => 'new tour wishlist'
          ];

          $new_wishlist = Wishlist::create($insert_data);
          return ApiResponse::success($new_wishlist, 'Wishlist item added successfully');

        }catch (\Exception $e) {
          return ApiResponse::error($e->getMessage());
        }

    }// end public function store(Request $request){
}

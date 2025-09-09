<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\User;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use App\Models\Tour;
use Exception;
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
    public function index(Request $r)
    {
        try{
            $traveler= Traveler::where('user_id',$r->id)->first();
            if($traveler){
                return ApiResponse::success($traveler->traveler_id, 'User Wishlist');
            }
            return ApiResponse::error( 'User Wishlist');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if(!isset($request->id)){
            return ApiResponse::error('id no presente');
        }

        $wishlist = Wishlist::query()
                    ->where('traveler_id',$request->id)
                    ->get();

        if (!$request->boolean('count')) {
            $wishlist->each->append('tour_data');
        }

       return ApiResponse::success($wishlist,'contenido de wishlist');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function travelerID(Request $r)
    {
        $traveler = Traveler::where('user_id', $r->id)->first();
        if($traveler){
            return ApiResponse::success($traveler->traveler_id,'ok');
        }
        return ApiResponse::error('not found');
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
            $existElement = Wishlist::where(['traveler_id' => $traveler->traveler_id, 'tour_id' => $tour_id])->first();
            
            if($existElement){
                return ApiResponse::error('Element already exists in wishlist');
            }
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


    public function delete(int $wishlist_id, Request $request){

			try{
					Wishlist::where('id', $wishlist_id)->delete();
					return ApiResponse::success([], 'Wishlist item deleted successfully');

			}catch (\Exception $e) {
					return ApiResponse::error($e->getMessage());
			}

    }

    public function deleteByTourId(Request $request){
        
        $tour_id = $request->has('tour_id') ? $request->post('tour_id') : null;
        $user_id = $request->has('user_id') ? $request->post('user_id') : null;

        if (empty($tour_id)) {
            return ApiResponse::error('Tour ID is missing');
        }
        if (empty($user_id)) {
            return ApiResponse::error('User ID is missing');
        }

        $traveler = Traveler::where('user_id', $user_id)->first();
        if (!$traveler) {
            return ApiResponse::error('Traveler not found for the given user ID');
        }

        $user = User::where('id', $user_id)->first();
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        try {

            $wishlistItem = Wishlist::where([
                'traveler_id' => $traveler->traveler_id,
                'tour_id' => $tour_id
            ])->first();

            if (!$wishlistItem) {
                return ApiResponse::error('Wishlist item not found');
            }

            $wishlistItem->delete();

            return ApiResponse::success([], 'Wishlist item removed successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting wishlist item: ' . $e->getMessage());
        }
    }
}

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
        // TODO: check if this is not necessary anymore
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
     * Get user's wishlist by user id (uses users table).
     *
     * Accepts:
     * - id (optional) : integer. If not provided, will use the authenticated user (if any).
     * - count (optional boolean) : if true, don't append tour_data
     */
    public function indexByUser(Request $request)
    {
        // Prefer explicit id param, otherwise fallback to currently authenticated user
        $userId = $request->input('id') ?: ($request->user() ? $request->user()->id : null);

        if (empty($userId)) {
            return ApiResponse::error('User ID is missing');
        }

        $user = User::find($userId);
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        // Use the relation defined on the User model
        $wishlist = $user->wishlists()->get();

        // Keep same behaviour as your show() helper: append tour_data only when count is not requested
        if (!$request->boolean('count')) {
            $wishlist->each->append('tour_data');
        }

        return ApiResponse::success($wishlist, 'User Wishlist');
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
                    ->where('user_id',$request->id)
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
        $userId = $r->input('id') ?: ($r>user() ? $r->user()->id : null);
        if (empty($userId)) {
            return ApiResponse::error('User ID is missing');
        }
        $user = User::find($userId);
        if( $user){
            return ApiResponse::success( $user->id,'ok');
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

        $user = User::find($user_id);
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        try{
            $existElement = Wishlist::where(['user_id' => $user->id, 'tour_id' => $tour_id])->first();
            
            if($existElement){
                $existElement->delete();
                return ApiResponse::success('Wishlist item removed successfully');
            }
            $insert_data = [
                'user_id' => $user->id,
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

        $user = User::find($user_id);
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        try {

            $wishlistItem = Wishlist::where([
                'user_id' => $user->id,
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

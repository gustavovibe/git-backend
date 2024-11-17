<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\User; 
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wishlists = Wishlist::all();
        return response()->json($wishlists);
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

    public function store(Request $request){

        $tour_id = $request->has('tourID') ? $request->post('tourID') : 0;
        $user_id =$request->has('userID') ? $request->post('userID') : 0;

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

        $user = User::where('id', $id)->first();
        if (!$user) {
            return ApiResponse::error('User not found');
        }

        try{

            $insert_data = [
                'traveler_id' => $traveler->traveler_id,
                'user_id' => $user->id,
                'wish_id' => 0,
                'tour_id' => $tour_id,
                'notes' => 'new tour wishlist'
            ];

            $new_wishlist = Wishlist::create($insert_data);
            return ApiResponse::success($newDestination, 'Destination created successfully');

        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }

    }// end public function store(Request $request){
}

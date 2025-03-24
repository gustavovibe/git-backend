<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use App\Models\ActionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Exception;
class TravelersController extends Controller
{

    /**
     * Get travelers.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function getTravelers(Request $request)
    {
        try{
            if ($request->has('traveler_id')) {
                $traveler_id = $request->query('traveler_id');
                $traveler = Traveler::where('traveler_id', $traveler_id)->with('user_:hear,internal_notes,suscribed,id')->first();
                $traveler->birth=Carbon::parse($traveler->birth)->format('Y-m-d');
                $traveler->issue=Carbon::parse($traveler->issue)->format('Y-m-d');
                $traveler->expire=Carbon::parse($traveler->expire)->format('Y-m-d');
                if ($traveler) {
                    return response()->json($traveler);
                } else {
                    return response()->json(['message' => 'Traveler not found'], 404);
                }
            } else {
                $travelers = Traveler::all();
                return response()->json($travelers);
            }
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }

    }


    /**
     * Write travelers.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function writeTravelers(Request $r)
    {

        try{
            $rules=[
              /*   'traveler_id' => 'required|string|max:255', */
                'title' => 'required|string|max:255',
                'gender' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'last' => 'required|string|max:255',
                'birth' => 'required',
                'passport' => 'required|integer',
                /* 'place' => 'required|string|max:255', */
                'issue' => 'required',
                'expire' => 'required',
                'mail' => 'required|string|email|max:255',
                'phone' => 'required|string|max:255',
                'phone_country' => 'required|string|max:5',
                'address' => 'required|string',
                'country' => 'required|string|max:255',
            ];

            $validator= Validator::make($r->all(),$rules);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ]);
            }


            $traveler= $r->traveler_id?Traveler::where('traveler_id',$r->traveler_id)->first():new Traveler();
            $traveler->fill([
                'title'=>$r->title,
                'gender' => $r->gender,
                'name' => $r->name,
                'last' => $r->last,
                'birth' =>Carbon::parse(strtotime($r->birth)),
                'passport' => $r->passport,
                'place' => $r->country,
                'issue' =>Carbon::parse(strtotime($r->issue)),
                'expire' =>Carbon::parse(strtotime($r->expire)),
                'mail' => $r->mail,
                'phone' => $r->phone,
                'phone_country' => $r->phone_country,
                'address' => $r->address,
                'country' => $r->country,
                'user_id'=>$r->user_id,
                'status'=>1,
            ])->save();

            $user= User::where('email',$traveler->mail)->first();
            if($user){
                $user->phone=$r->phone;
                $user->phone_country=$r->phone_country;
                $user->country=$r->country;
                $user->save();
            }

            ActionLog::create([
                'user_id' => $r->user_log,
                'type' => $r->traveler_id? 'Update':'Create',
                'action' => $r->traveler_id? 'Traveler update successfully':'Traveler created successfully',
                'item' => 'Traveler',
            ]);

            return ApiResponse::success($traveler);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }

    }

    /**
     * Get traveler data.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function getTravelerData(Request $request)
    {
        try{
            $travelers = Traveler::where('lead', 1)->get();

            $result = [];

            foreach ($travelers as $traveler) {
                $orders = $traveler->orders;

                if ($orders->isEmpty()) {
                    continue;
                }

                $totalPaid = 0;
                $totalCommission = 0;
                $totalDuration = 0;
                $totalOrders = $orders->count();
                $totalGroupSize = 0;
                $lastBookingDate = null;
                $firstBookingDate = null;
                $lastBookingStartCity = null;

                $ordersData = [];

                foreach ($orders as $order) {
                    $totalPaid += $order->paid;
                    $totalCommission += $order->commission;
                    $totalDuration += $order->duration;
                    $totalGroupSize += $order->travelers_number;

                    if (!$lastBookingDate || $order->start > $lastBookingDate) {
                        $lastBookingDate = $order->start;
                        $lastBookingStartCity = $order->start_city;
                    }

                    if (!$firstBookingDate || $order->created_at < $firstBookingDate) {
                        $firstBookingDate = $order->created_at;
                    }

                    $ordersData[] = [
                        'start' => $order->start,
                        'created_at' => $order->created_at,
                        'departure' => $order->departure,
                        'duration' => $order->duration,
                        'tour_length' => $order->tour_length,
                        'start_city' => $order->start_city,
                        'group_size' => $order->travelers_number,
                        'tour_id' => $order->tour_id,
                        'operator' => $order->operator,
                        'f_duration' => $order->f_duration,
                        'total_stops' => $order->total_stops,
                        'checked_bags' => $order->checked_bags,
                        'paid' => $order->paid,
                        'commission' => $order->commission,
                    ];
                }

                $result[] = [
                    'name' => $traveler->name,
                    'country' => $traveler->country,
                    'birth' => $traveler->birth,
                    'age' => Carbon::parse($traveler->birth)->age,
                    'orders' => $ordersData,
                    'last_booking_start_city' => $lastBookingStartCity,
                    'group_size_average' => $totalGroupSize / $totalOrders,
                    'last_booking_date' => $lastBookingDate,
                    'first_booking_date' => $firstBookingDate,
                    'total_paid' => $totalPaid,
                    'average_duration' => $totalDuration / $totalOrders,
                    'average_commission' => $totalCommission / $totalOrders,
                    'gross_profit' => $totalPaid * ($totalCommission / $totalOrders),
                    'frequency' => $totalOrders / max(Carbon::parse($firstBookingDate)->diffInYears($lastBookingDate), 1),
                    'total_orders' => $totalOrders,
                ];
            }

            return ApiResponse::success($result);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Update.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @param int $id ID
     * @return array
     */
    public function update(Request $r, $id)
    {
        try{
            $traveler = Traveler::findOrFail($id);

            $data = $r->only(['title', 'name', 'last', 'birth', 'country']);

            $traveler->update($data);

            ActionLog::create([
                'user_id' => $r->user_log,
                'type' => 'Update',
                'action' =>'User update successfully',
                'item' => 'Traveler',
            ]);

            return response()->json([
                'message' => 'Traveler updated successfully',
                'traveler' => $traveler
            ], 200);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }

    }

    /**
     * Destroy.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @param int $id ID
     * @return array
     */
    public function destroy(Request $r,$id)
    {
        try{
            $traveler = Traveler::findOrFail($id);
            $traveler->status=0;
            $traveler->save();

            ActionLog::create([
                'user_id' => $r->user_log,
                'type' => 'Deleted',
                'action' =>'Traveler deleted successfully',
                'item' => 'Traveler',
            ]);
            return response()->json([
                'message' => 'Traveler deleted successfully',
            ], 200);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Traveler id.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function traveler_id(Request $r){
        try{
            $traveler = Traveler::where('user_id',$r->user_id)->first();
            return response()->json(['success'=>true,'data'=>$traveler->traveler_id]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    public function show(Request $r){
        try{
            $traveler=Traveler::query();
            !$r->user_id?:$traveler->where('user_id',$r->user_id);
            return ApiResponse::success($traveler->first());
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }
}

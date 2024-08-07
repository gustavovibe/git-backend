<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Error;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserById(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'id query parameter is required.'
            ], 400);
        }

        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Customize the attributes you want to return
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country' => $user->country,
            'role' => $user->role,
            'active' => $user->active,
            'suscribed' => $user->suscribed,
            'hear' => $user->hear,
        ];

        return response()->json([
            'status' => true,
            'user' => $userData
        ], 200);
    }

    public function getUsersWithOrders(Request $request)
    {
        $users = User::whereHas('orders')->get();

        $result = [];

        foreach ($users as $user) {
            $traveler = Traveler::where('mail', $user->email)->first();

            if (!$traveler) {
                continue;
            }

            $orders = $user->orders;

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
    }

}

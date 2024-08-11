<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use Carbon\Carbon;

class TravelersController extends Controller
{
    // Method to get travelers
    public function getTravelers(Request $request)
    {
        if ($request->has('traveler_id')) {
            $traveler_id = $request->query('traveler_id');
            $traveler = Traveler::where('traveler_id', $traveler_id)->first();

            if ($traveler) {
                return response()->json($traveler);
            } else {
                return response()->json(['message' => 'Traveler not found'], 404);
            }
        } else {
            $travelers = Traveler::all();
            return response()->json($travelers);
        }
    }


    // Method to write travelers
    public function writeTravelers(Request $request)
    {
        $request->validate([
            'traveler_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'last' => 'required|string|max:255',
            'birth' => 'required|date',
            'passport' => 'required|integer',
            'place' => 'required|string|max:255',
            'issue' => 'required|date',
            'expire' => 'required|date',
            'mail' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string',
            'country' => 'required|string|max:255',
            'lead' => 'required|string|max:255',
        ]);

        $traveler = Traveler::create($request->all());

        return response()->json($traveler, 201);
    }

    public function getTravelerData(Request $request)
    {
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
    }
}

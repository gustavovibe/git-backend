<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'booking_id' => 'required|string',
            'booking_status' => 'required|string',
            'departure' => 'required|date',
            'start' => 'required|date',
            'arrival' => 'required|date',
            'end' => 'required|date',
            'duration' => 'required|integer',
            'tour_length' => 'required|integer',
            'tour_name' => 'required|string',
            'tour_id' => 'required|integer',
            'style' => 'required|integer',
            'operator' => 'required|integer',
            'start_city' => 'required|integer',
            'end_city' => 'required|integer',
            'duffel_id' => 'required|string',
            'duffel_status' => 'required|string',
            'tourradar_id' => 'required|string',
            'tourradar_status' => 'required|string',
            'tourradar_reason' => 'required|string',
            'tourradar_text' => 'required|string',
            'source' => 'required|string',
            'device' => 'required|string',
            'affiliate' => 'required|integer',
            'origin' => 'required|string',
            'f_destination' => 'required|integer',
            'f_return' => 'required|integer',
            'f_duration' => 'required|integer',
            'destination_stops' => 'required|integer',
            'return_stops' => 'required|integer',
            'total_stops' => 'required|integer',
            'destination_carrier' => 'required|string',
            'return_carrier' => 'required|string',
            'checked_bags' => 'required|integer',
            'travelers_number' => 'required|integer',
            'reference' => 'required|string',
            'method' => 'required|string',
            'currency' => 'required|string',
            'invoice' => 'required|string',
            'paid' => 'required|numeric',
            'fees' => 'required|numeric',
            'markup' => 'required|numeric',
            'refunded' => 'required|numeric',
            'p_flight' => 'required|numeric',
            'p_tour' => 'required|numeric',
            'discounted' => 'required|numeric',
            'promo' => 'required|string',
            'profit' => 'required|numeric',
            'ratio' => 'required|numeric',
            'user_id' => 'required|string|max:255',
        ]);

        // Log the validated data for debugging
        // \Log::info('Validated Data:', $validatedData);

        // Create a new order
        $order = Order::create($validatedData);

        if ($request->has('traveler_ids')) {
            $order->travelers()->attach($request->input('traveler_ids'));
        }

        // Log the created order for debugging
        // \Log::info('Created Order:', $order->toArray());

        // Return a response
        return response()->json($order, 201);
    }

    public function adminOrders(Request $request)
        {
            $query = Order::query();

            if ($request->has('created')) {
                $dates = explode('-', $request->query('created'));
                if (count($dates) == 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }

            if ($request->has('departure')) {
                $dates = explode('-', $request->query('departure'));
                if (count($dates) == 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $query->whereBetween('departure', [$startDate, $endDate]);
                }
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->query('user_id'));
            }    

            if ($request->query('travelers') == 'true') {
                $query->with('travelers');
            }

            $orders = $query->get();

            return response()->json($orders);
        }

    public function getOrders(Request $request)
    {
        $today = date('Y-m-d');

        // Query for orders with departure dates before today
        $beforeTodayQuery = Order::query()
            ->where('departure', '<', $today);

        // Query for orders with departure dates after today
        $afterTodayQuery = Order::query()
            ->where('departure', '>=', $today);

        if ($request->has('created')) {
            $dates = explode('-', $request->query('created'));
            if (count($dates) == 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d', strtotime($dates[1]));
                $beforeTodayQuery->whereBetween('created_at', [$startDate, $endDate]);
                $afterTodayQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($request->has('user_id')) {
            $userId = $request->query('user_id');
            $beforeTodayQuery->where('user_id', $userId);
            $afterTodayQuery->where('user_id', $userId);
        }

        if ($request->query('travelers') == 'true') {
            $beforeTodayQuery->with('travelers');
            $afterTodayQuery->with('travelers');
        }

        // Paginate the results (3 per page)
        $beforeTodayOrders = $beforeTodayQuery->paginate(3, ['*'], 'before_page');
        $afterTodayOrders = $afterTodayQuery->paginate(3, ['*'], 'after_page');

        // Return the results as a combined JSON response
        return response()->json([
            'before_today' => $beforeTodayOrders,
            'after_today' => $afterTodayOrders,
        ]);
    }


    public function getOrderWithTravelers(Request $request, $booking_id)
    {
        $includeTravelers = $request->query('travelers') == 'true';

        if ($includeTravelers) {
            $order = Order::with('travelers')->where('booking_id', $booking_id)->first();
        } else {
            $order = Order::where('booking_id', $booking_id)->first();
        }

        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }
    
}

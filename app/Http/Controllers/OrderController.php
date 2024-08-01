<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlightTourResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\ApiResponse;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;

        $date = $request->input('date');

        if ($date) {
            $paginatedData = Order::with('flightTour')->where('name', 'like', $date . '%')->paginate($perPage);
        } else {
            $paginatedData = Order::with('flightTour')->paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = OrderResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    public function adminReports(Request $request)
    {
        $filters = $request->only([
            'fechaInicio', 'fechaFin', 'destinations',
            'operator', 'adventure', 'status',
            'duration_adventure', 'duration_whole_trip',
            'carrier'
        ]);

        $orders = Order::filter($filters)->get();

        $totalOrders = Order::count();
        $totalSales = 0;
        $totalPrice = 0;
        $numberOfPeople = 0;
        $totalDays = 0;
        $totalPaidToSuppliers = 0;
        $totalRefunded = 0;
        $totalDiscount = 0;
        $totalGrossProfit = 0;

        foreach ($orders as $order) {
            $totalSales += $order->paid;
            $totalPrice += $order->p_tour;
            $numberOfPeople += $order->travelers_number;

            $totalPaidToSuppliers += $order->paid_to_suppliers;
            $totalRefunded += $order->refunded;
            $totalDiscount += $order->discounted;

            $startDate = Carbon::parse($order->start);
            $endDate = Carbon::parse($order->end);
            $days = $startDate->diffInDays($endDate) + 1;
            $totalDays += $days * $order->travelers_number;

            $grossProfit = $order->paid - $order->paid_to_suppliers - $order->refunded;
            $totalGrossProfit += $grossProfit;
        }
        $averageSalesPerPerson = $totalSales / $numberOfPeople;

        $averagePricePerPersonPerDay = $totalPrice / $totalDays;

        $grossProfitRatio = ($totalGrossProfit / $totalSales) * 100;

        return ApiResponse::success([[
            'total_sales' => $totalSales,
            'orders' => $totalOrders,
            'travelers' => $numberOfPeople,
            'average_sales' => $averageSalesPerPerson,
            'average_price' => $averagePricePerPersonPerDay,
            'suppliers_paid' => $totalPaidToSuppliers,
            'refunded' => $totalRefunded,
            'discount' => $totalDiscount,
            'gross_profit' => $totalGrossProfit,
            'profit_ratio' => $grossProfitRatio,
        ]]);
    }

    public function store(Request $request)
    {
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

        $order = Order::create($validatedData);

        if ($request->has('traveler_ids')) {
            $order->travelers()->attach($request->input('traveler_ids'));
        }

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

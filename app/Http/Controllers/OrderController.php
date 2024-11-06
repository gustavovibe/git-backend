<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlightTourResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;

        $date = $request->input('date');

        if ($date) {
            $paginatedData = Order::with(['flightTour', 'travelers', 'user', 'tour'])->where('name', 'like', $date . '%')->paginate($perPage);
        } else {
            $paginatedData = Order::with(['flightTour', 'travelers', 'user', 'tour'])->paginate($perPage);
        }
        $responseData = $paginatedData->toArray();

        $responseData['data'] = OrderResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    public function getOrder($id)
    {
        $order = Order::with(['flightTour', 'travelers', 'user'])->find($id);
        return ApiResponse::success(new OrderResource($order));
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

    // Filter by created_at date range
    if ($request->has('created_at')) {
        $dateRange = explode(',', $request->input('created_at'));
        if (count($dateRange) === 2) {
            $startDate = Carbon::parse($dateRange[0])->startOfDay();
            $endDate = Carbon::parse($dateRange[1])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }

    // Filter by departure date range
    if ($request->has('departure')) {
        $dates = explode(',', $request->input('departure'));
        if (count($dates) === 2) {
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = Carbon::parse($dates[1])->endOfDay();
            $query->whereBetween('departure', [$startDate, $endDate]);
        }
    }

    // Filter by cities
    if ($request->has('cities')) {
        $cities = explode(',', $request->input('cities'));
        $query->whereHas('tour.cities', function ($q) use ($cities) {
            $q->whereIn('t_city_id', $cities);
        });
    }

    // Filter by countries
    if ($request->has('countries')) {
        $countries = explode(',', $request->input('countries'));
        $query->whereHas('tour.countries', function ($q) use ($countries) {
            $q->whereIn('t_country_id', $countries);
        });
    }

    // Filter by natural destinations
    if ($request->has('natural_destinations')) {
        $naturalDestinations = explode(',', $request->input('natural_destinations'));
        $query->whereHas('tour.natural_destination', function ($q) use ($naturalDestinations) {
            $q->whereIn('t_natural_id', $naturalDestinations);
        });
    }

    // Filter by types
    if ($request->has('types')) {
        $types = explode(',', $request->input('types'));
        $query->whereHas('tour.type', function ($q) use ($types) {
            $q->whereIn('tour_type_id', $types);
        });
    }

    // Filter by operators
    if ($request->has('operators')) {
        $operators = explode(',', $request->input('operators'));
        $query->whereIn('operator', $operators);
    }

    // Filter by duration range
    if ($request->has('duration')) {
        $durations = explode('-', $request->input('duration'));
        $query->whereBetween('duration', [trim($durations[0]), trim($durations[1])]);
    }

    // Filter by total_orders range
    if ($request->has('total_orders')) {
        $totalOrders = explode('-', $request->input('total_orders'));
        $query->whereHas('user', function ($q) use ($totalOrders) {
            $q->select('id', DB::raw('COUNT(*) as total_orders'))
              ->groupBy('id')
              ->havingRaw('total_orders BETWEEN ? AND ?', [(int)$totalOrders[0], (int)$totalOrders[1]]);
        });
    }

    // Filter by total_paid range
    if ($request->has('total_paid')) {
        $totalPaid = explode('-', $request->input('total_paid'));
        $query->whereHas('user', function ($q) use ($totalPaid) {
            $q->select('id', DB::raw('SUM(paid) as total_paid'))
              ->groupBy('id')
              ->havingRaw('total_paid BETWEEN ? AND ?', [(int)$totalPaid[0], (int)$totalPaid[1]]);
        });
    }

    // Filter by frequency range
    if ($request->has('frequency')) {
        $frequencies = explode('-', $request->input('frequency'));
        $query->whereHas('user', function ($q) use ($frequencies) {
            $q->select('id', DB::raw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) as frequency'))
              ->groupBy('id')
              ->havingRaw('frequency BETWEEN ? AND ?', [(int)$frequencies[0], (int)$frequencies[1]]);
        });
    }

    // Filter by age range
    if ($request->has('age')) {
        $ageRange = explode('-', $request->input('age'));
        $query->whereHas('user.traveler', function ($q) use ($ageRange) {
            $q->whereBetween(DB::raw('TIMESTAMPDIFF(YEAR, birth, CURDATE())'), [(int)$ageRange[0], (int)$ageRange[1]]);
        });
    }

    // Filter by gender
    if ($request->has('gender')) {
        $gender = $request->input('gender');
        $query->whereHas('user.traveler', function ($q) use ($gender) {
            $q->where('gender', $gender);
        });
    }

    // Filter by country
    if ($request->has('country')) {
        $country = $request->input('country');
        $query->whereHas('user', function ($q) use ($country) {
            $q->where('country', $country);
        });
    }

    // Filter by specific tour IDs
    if ($request->has('tours')) {
        $tourIds = explode(',', $request->input('tours'));
        $query->whereIn('tour_id', $tourIds);
    }

    // Include travelers if requested
    if ($request->query('travelers') === 'true') {
        $query->with('travelers');
    }

    // Paginate and return the result
    $paginatedData = $query->paginate($request->input('per_page', 10));
    return ApiResponse::success($paginatedData);
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
            $beforeTodayQuery->with('operator');
            $afterTodayQuery->with('travelers');
            $afterTodayQuery->with('operator');
        }

        // Paginate the results (3 per page)
        $beforeTodayOrders = $beforeTodayQuery->paginate(3, ['*'], 'before_page');
        $afterTodayOrders = $afterTodayQuery->paginate(3, ['*'], 'after_page');

        // Return the results as a combined JSON response
        return response()->json([
            'status' => true,
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

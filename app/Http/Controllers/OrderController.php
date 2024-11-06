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

        if ($request->has('created_at')) {
            $dateRange = explode(',', $request->input('created_at'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $query->whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        if ($request->has('departure')) {
            $dates = explode(',', $request->input('departure'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
            $query->whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('departure', [$startDate, $endDate]);
            });
        }

        if ($request->has('cities')) {
            $cities = explode(',', $request->input('cities'));
            $query->whereHas('orders.tour.cities', function ($q) use ($cities) {
                $q->whereIn('t_city_id', $cities);
            });
        }

        if ($request->has('countries')) {
            $countries = explode(',', $request->input('countries'));
            $query->whereHas('orders.tour.countries', function ($q) use ($countries) {
                $q->whereIn('t_country_id', $countries);
            });
        }

        if ($request->has('natural_destinations')) {
            $naturalDestinations = explode(',', $request->input('natural_destinations'));
            $query->whereHas('orders.tour.natural_destination', function ($q) use ($naturalDestinations) {
                $q->whereIn('t_natural_id', $naturalDestinations);
            });
        }

        if ($request->has('types')) {
            $types = explode(',', $request->input('types'));
            $query->whereHas('orders.tour.type', function ($q) use ($types) {
                $q->whereIn('tour_type_id', $types);
            });
        }

        if ($request->has('operators')) {
            $operators = explode(',', $request->input('operators'));
            $query->whereHas('orders', function ($q) use ($operators) {
                $q->whereIn('operator', $operators);
            });
        }

        if ($request->has('duration')) {
            $durations = explode('-', $request->input('duration'));
            $query->whereHas('orders', function ($q) use ($durations) {
                $q->whereBetween('duration', [trim($durations[0]), trim($durations[1])]);
            });
        }

        
        if ($request->has('average_duration')) {
            $averageDurations = explode('-', $request->input('average_duration'));
            $query->whereHas('orders', function ($q) use ($averageDurations) {
                $q->select('user_id', DB::raw('AVG(duration) as avg_duration'))
                    ->groupBy('user_id')
                    ->havingBetween('avg_duration', [trim($averageDurations[0]), trim($averageDurations[1])]);
            });
        }

        if ($request->has('stops')) {
            $stops = explode('-', $request->input('stops'));
            $query->whereHas('orders', function ($q) use ($stops) {
                $q->whereBetween('total_stops', [trim($stops[0]), trim($stops[1])]);
            });
        }

        if ($request->has('f_duration')) {
            $fDurations = explode('-', $request->input('f_duration'));
            $query->whereHas('orders', function ($q) use ($fDurations) {
                $q->whereBetween('f_duration', [trim($fDurations[0]), trim($fDurations[1])]);
            });
        }

        if ($request->has('total_orders')) {
            $totalOrders = explode('-', $request->input('total_orders'));
            $query->whereHas('orders', function ($q) use ($totalOrders) {
                $q->select('user_id', DB::raw('COUNT(*) as total_orders'))
                    ->groupBy('user_id')
                    ->havingBetween('total_orders', [trim($totalOrders[0]), trim($totalOrders[1])]);
            });
        }

        if ($request->has('total_paid')) {
            $totalPaid = explode('-', $request->input('total_paid'));
            $query->whereHas('orders', function ($q) use ($totalPaid) {
                $q->select('user_id', DB::raw('SUM(paid) as total_paid'))
                    ->groupBy('user_id')
                    ->havingBetween('total_paid', [trim($totalPaid[0]), trim($totalPaid[1])]);
            });
        }

        if ($request->has('frequency')) {
            $frequencies = explode('-', $request->input('frequency'));
            $minFrequency = trim($frequencies[0]);
            $maxFrequency = trim($frequencies[1]);

            $query->whereHas('orders', function ($q) use ($minFrequency, $maxFrequency) {
                $q->select('user_id', DB::raw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) as frequency'))
                    ->groupBy('user_id')
                    ->havingRaw('frequency BETWEEN ? AND ?', [$minFrequency, $maxFrequency]);
            });
        }

        // Filter by age range
        if ($request->has('age')) {
            $ageRange = explode('-', $request->input('age'));
            $minAge = $ageRange[0];
            $maxAge = $ageRange[1];

            $query->whereHas('orders', function ($q) use ($minAge, $maxAge) {
                $q->whereHas('user', function ($q) use ($minAge, $maxAge) {
                    $q->whereHas('traveler', function ($q) use ($minAge, $maxAge) {
                        $q->whereBetween(DB::raw('TIMESTAMPDIFF(YEAR, birth, CURDATE())'), [(int)$minAge, (int)$maxAge]);
                    });
                });
            });
        }

        // Filter by gender
        if ($request->has('gender')) {
            $gender = $request->input('gender');

            $query->whereHas('orders', function ($q) use ($gender) {
                $q->whereHas('user', function ($q) use ($gender) {
                    $q->whereHas('traveler', function ($q) use ($gender) {
                        $q->where('gender', $gender);
                    });
                });
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $country = $request->input('country');
            $query->whereHas('user', function ($q) use ($country) {
                $q->where('country', $country);
            });
        }

            // Filter by tour IDs
        if ($request->has('tours')) {
            $tourIds = explode(',', $request->input('tours'));
            $query->whereHas('orders.tour', function ($q) use ($tourIds) {
                $q->whereIn('tour_id', $tourIds);
            });
        }

        if ($request->has('created')) {
            $dates = explode('-', $request->query('created'));
            if (count($dates) == 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d', strtotime($dates[1]));
                $query->whereBetween('created_at', [$startDate, $endDate]);
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

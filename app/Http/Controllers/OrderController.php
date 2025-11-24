<?php

namespace App\Http\Controllers;

use App\Filters\ToursFilters;
use App\Http\Resources\FlightTourResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\ApiResponse;
use App\Mail\BookingMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Attempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


class OrderController extends Controller
{

    /**
     * Get all orders.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function index(Request $r)
    {
        $orders = (new ToursFilters)->OrdersAll($r, 0);

        // If user asks for links, attach them
        if ($r->query('links') === 'true') {
            Log::info('orders-all: links=true requested; orders type: ' . gettype($orders) . (is_object($orders) ? ' class:' . get_class($orders) : ''));
        
            // LengthAwarePaginator (paged Eloquent results)
            if ($orders instanceof LengthAwarePaginator) {
                Log::info('orders-all: handling LengthAwarePaginator; items on page: ' . $orders->count());
                $collection = $orders->getCollection()->map(function ($order) {
                    $order->tourradar_booking_link = $this->extractTourRadarLinkFromOrder($order);
                    return $order;
                });
                $orders->setCollection($collection);
                return $orders;
            }
        
            // Eloquent Collection
            if ($orders instanceof Collection) {
                Log::info('orders-all: handling Collection; items: ' . $orders->count());
                $orders = $orders->map(function ($order) {
                    $order->tourradar_booking_link = $this->extractTourRadarLinkFromOrder($order);
                    return $order;
                });
                return $orders;
            }
        
            // Plain array
            if (is_array($orders)) {
                Log::info('orders-all: handling array; items: ' . count($orders));
                $orders = array_map(function ($order) {
                    $obj = is_array($order) ? (object)$order : $order;
                    $obj->tourradar_booking_link = $this->extractTourRadarLinkFromOrder($obj);
                    return $obj;
                }, $orders);
                return $orders;
            }
        
            // Fallback: JSON-encode/decode attempt
            Log::info('orders-all: fallback branch - attempting json decode/cast');
            try {
                $decoded = json_decode(json_encode($orders));
                if (is_array($decoded) || is_object($decoded)) {
                    foreach ($decoded as $k => $order) {
                        $decoded[$k]->tourradar_booking_link = $this->extractTourRadarLinkFromOrder($order);
                    }
                }
                return $decoded;
            } catch (\Throwable $e) {
                Log::error('orders-all: fallback failed: ' . $e->getMessage());
                return $orders;
            }
        }
        

        return $orders;
    }

    /**
     * Helper: extract the TourRadar booking link for a given $order.
     *
     * - Looks up latest Attempt with same booking_id if attempts not loaded.
     * - Uses Attempt::tourradar_res (cast to array) to find a link with type == 'booking-page'
     * - Falls back to first url if no 'booking-page' found.
     */
    protected function extractTourRadarLinkFromOrder($order)
    {
        try {
            $bookingId = $order->booking_id ?? ($order['booking_id'] ?? null);
            Log::info("extractTourRadarLinkFromOrder: start for booking_id: " . ($bookingId ?? 'null'));
    
            // Prefer preloaded attempts if available
            $attempt = null;
            if (isset($order->attempts) && is_iterable($order->attempts)) {
                Log::info('extractTourRadarLinkFromOrder: attempts preloaded count: ' . count($order->attempts));
                // booking_id is unique, so just grab the first
                $attempt = collect($order->attempts)->first();
            }
    
            // If not preloaded, query the attempts table (safe single query per order)
            if (!$attempt && $bookingId) {
                $attempt = Attempt::where('booking_id', $bookingId)
                                  ->orderByDesc('created_at')
                                  ->first();
                Log::info('extractTourRadarLinkFromOrder: attempt queried from DB: ' . ($attempt ? 'found' : 'not found'));
            }
    
            if (!$attempt) {
                Log::info('extractTourRadarLinkFromOrder: no attempt found for booking_id: ' . $bookingId);
                return null;
            }
    
            $tr = $attempt->tourradar_res ?? null;
            Log::info('extractTourRadarLinkFromOrder: tourradar_res raw: ' . (is_string($tr) ? $tr : json_encode($tr)));
    
            // If it's a JSON string, decode it
            if (is_string($tr)) {
                $tr = json_decode($tr, true);
            }
    
            if (!is_array($tr)) {
                Log::warning('extractTourRadarLinkFromOrder: tourradar_res is not an array after decode for booking_id: ' . $bookingId);
                return null;
            }
    
            // Minimal assumption: URL is at links[0].url
            if (isset($tr['links']) && is_array($tr['links']) && isset($tr['links'][0]['url'])) {
                Log::info('extractTourRadarLinkFromOrder: found links[0].url for booking_id: ' . $bookingId);
                return $tr['links'][0]['url'];
            }
    
            // Fallback: find any url inside links array
            if (isset($tr['links']) && is_array($tr['links'])) {
                foreach ($tr['links'] as $l) {
                    if (is_array($l) && !empty($l['url'])) {
                        Log::info('extractTourRadarLinkFromOrder: found fallback url in links for booking_id: ' . $bookingId);
                        return $l['url'];
                    }
                }
            }
    
            Log::info('extractTourRadarLinkFromOrder: no url found in tourradar_res for booking_id: ' . $bookingId);
            return null;
    
        } catch (\Throwable $e) {
            Log::error('extractTourRadarLinkFromOrder: error for booking_id ' . ($order->booking_id ?? 'null') . ' -> ' . $e->getMessage());
            return null;
        }
    }
    

    /**
     * Get all orders in CSV format.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function ordersCsv(Request $r){
        $orders= (new ToursFilters)->OrdersAll($r,1);
      /*   return $orders; */
        $filename = 'orders_' . now()->format('Ymd_His') . '.csv';
        $columns = ['Booking Date', 'Order#', 'Tour Operator', 'Booking status', 'Travelers name', 'Travelers country','Paid by traveler','Source','Paid by us','Refund','Gross profit ratio (%)'];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->start,
                    $order->booking_id,
                    $order->operator,
                    $order->booking_status,
                    $order->user->name,
                    $order->country,
                    $order->paid,
                    $order->country,
                    $order->channel,
                    $order->paid,
                    $order->refund,
                    $order->grossProfitRatio,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ]);
        return $orders;
    }

    /**
     * Get Order by ID.
     *
     * This endpoint will return the order with the given ID.
     *
     * Updated at 09/12/2024 (user)
     *
     * @param int $id id for the order
     *
     */
    public function getOrder($id)

    {
        $order = Order::with(['flightTour', 'travelers', 'user'])->find($id);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => "Ok",
        ], 200);
    }

    /**
     * Get admin reports.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     *
     */
    public function adminReports(Request $request)
    {
        try{
            $booking_dates = $request->input('booking');
            $travel_dates = $request->input('travel');
            $destination_cities = $request->input('destination_city');
            $destination_countries = $request->input('destination_country');
            $travel_styles = $request->input('travel_style');
            $operators = $request->input('operator');
            $adventures = $request->input('adventure');
            $status = $request->input('status');
            $duration = $request->input('duration');
            $whole_trip = $request->input('whole_trip');
            $age_group = $request->input('age_group');
            $genders = $request->input('gender');
            $group_size = $request->input('group_size');
            $countries = $request->input('country');

            $orders = Order::
                        when($booking_dates, function ($query) use ($booking_dates) {
                            return $query->whereBetween('created_at', $booking_dates);
                        })
                        ->when($travel_dates, function ($query) use ($travel_dates) {
                            return $query->whereBetween('start', $travel_dates);
                        })
                        ->when($destination_cities, function ($query) use ($destination_cities) {
                            return $query->whereHas('tour.cities', function ($q) use ($destination_cities) {
                                $q->whereIn('t_city_id', $destination_cities);
                            });
                        })
                        ->when($destination_countries, function ($query) use ($destination_countries) {
                            return $query->whereHas('tour.countries', function ($q) use ($destination_countries) {
                                $q->whereIn('t_country_id', $destination_countries);
                            });
                        })
                        ->when($travel_styles, function ($query) use ($travel_styles) {
                            return $query->whereHas('tour.type', function ($q) use ($travel_styles) {
                                $q->whereIn('tour_type_id', $travel_styles);
                            });
                        })
                        ->when($operators, function ($query) use ($operators) {
                            return $query->whereHas('operator', function ($q) use ($operators) {
                                $q->whereIn('operator_id', $operators);
                            });
                        })
                        ->when($adventures, function ($query) use ($adventures) {
                            return $query->whereHas('tour.natural_destination', function ($q) use ($adventures) {
                                $q->whereIn('t_natural_id', $adventures);
                            });
                        })
                        ->when($status, function ($query) use ($status) {
                            return $query->whereIn('booking_status', $status);
                        })
                        ->when($duration, function ($query) use ($duration) {
                            return $query->whereBetween('tour_length', explode('-', $duration));
                        })
                        ->when($whole_trip, function ($query) use ($whole_trip) {
                            return $query->whereBetween('whole_trip', explode('-', $whole_trip));
                        })
                        ->when($age_group, function ($query) use ($age_group) {
                            return $query->whereBetween('age_group', explode('-', $age_group));
                        })
                        ->when($genders, function ($query) use ($genders) {
                            return $query->whereIn('gender', $genders);
                        })
                        ->when($group_size, function ($query) use ($group_size) {
                            if($group_size >= 11){
                                return $query->where('group_size', '>=', $group_size);
                            }
                            return $query->where('group_size', $group_size);
                        })
                        ->when($countries, function ($query) use ($countries) {
                            return $query->whereIn('country', $countries);
                        })
                        ->get();

            $totalOrders = $orders->count();
            $totalSales = 0;
            $totalPrice = 0;
            $numberOfPeople = 0;
            $totalDays = 0;
            $totalPaidToSuppliers = 0;
            $totalRefunded = 0;
            $totalDiscount = 0;
            $totalGrossProfit = 0;

            $monthlySalesData = [];

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
                $month = $order->created_at->format('Y-m');
                $channel = $order->channel;

                if (!isset($monthlySalesData[$month])) {
                    $monthlySalesData[$month] = [
                        'Direct' => 0,
                        'web' => 0,
                        'Affiliates' => 0,
                        'Referrals' => 0,
                    ];
                }

                $monthlySalesData[$month][$channel] += $order->paid;

            }
            $averageSalesPerPerson = ($numberOfPeople > 0) ?$totalSales / $numberOfPeople:0;

            $averagePricePerPersonPerDay = ($totalDays > 0) ?$totalPrice / $totalDays:0;

            $grossProfitRatio = ($totalSales > 0) ? ($totalGrossProfit / $totalSales) * 100 : 0;

            

            $chartData = [
                'labels' => array_keys($monthlySalesData),
                'datasets' => [
                    [
                        'type' => 'bar',
                        'label' => 'Direct',
                        'backgroundColor' => '#FFA726',
                        'data' => array_column($monthlySalesData, 'Direct'),
                    ],
                    [
                        'type' => 'bar',
                        'label' => 'Web',
                        'backgroundColor' => 'blue',
                        'data' => array_column($monthlySalesData, 'web'),
                    ],
                    [
                        'type' => 'bar',
                        'label' => 'Affiliates',
                        'backgroundColor' => '#66BB6A',
                        'data' => array_column($monthlySalesData, 'Affiliates'),
                    ],
                    [
                        'type' => 'bar',
                        'label' => 'Referrals',
                        'backgroundColor' => '#FFEB3B',
                        'data' => array_column($monthlySalesData, 'Referrals'),
                    ]
                ]
            ];

            return ApiResponse::success([[
                    'total_sales' =>'$'.number_format( $totalSales,2),
                    'orders' => $totalOrders,
                    'travelers' => $numberOfPeople,
                    'average_sales' => '$'.number_format($averageSalesPerPerson,2),
                    'average_price' => '$'.number_format($averagePricePerPersonPerDay,2),
                    'suppliers_paid' => '$'.number_format($totalPaidToSuppliers,2),
                    'refunded' => '$'.number_format($totalRefunded,2),
                    'discount' => '$'.number_format($totalDiscount,2),
                    'gross_profit' => '$'.number_format($totalGrossProfit,2),
                    'profit_ratio' => $grossProfitRatio.'%',
                    'chart_data' => $chartData,
                ],
            ]);

        }catch(Exception $e){
            return response()->json(['sucesss'=>false,'data'=>$e]);
        }
    }

    /**
     * Store a new order.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
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
        \Log::info('email  package controller  Order controller' );
        TourController::emailBConfirmation($order->booking_id,$order->duffer_id);
        \Log::info('email  package controller sent Order controller' );
        return response()->json($order, 201);
    }

    /**
     * Get admin orders.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
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

    /**
     * Get orders.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
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

    /**
     * Get order with travelers.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @param int $booking_id Booking ID
     * @return array
     */
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

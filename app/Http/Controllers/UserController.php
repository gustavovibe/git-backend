<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Error;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use Carbon\Carbon;
<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
=======
use DB;
>>>>>>> 5923969 (user orders filters errors corrected)
=======

>>>>>>> parent of 5923969 (user orders filters errors corrected)

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
<<<<<<< HEAD
{
    $query = User::query();

    // Filter by creation date range
    if ($request->has('created_at')) {
        [$startDate, $endDate] = explode('-', $request->input('created_at'));
        $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
    }

    // Fetch users who have orders
    $query->whereHas('orders', function($query) use ($request) {
        // Filter by departure date range
        if ($request->has('departure')) {
            [$startDate, $endDate] = explode('-', $request->input('departure'));
            $query->whereBetween('departure', [Carbon::parse($startDate), Carbon::parse($endDate)]);
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
            [$minDuration, $maxDuration] = explode('-', $request->input('duration'));
            $query->whereBetween('duration', [(int)$minDuration, (int)$maxDuration]);
        }

        // Filter by average duration range
        if ($request->has('average_duration')) {
            [$minAvgDuration, $maxAvgDuration] = explode('-', $request->input('average_duration'));
            $query->havingRaw('AVG(duration) BETWEEN ? AND ?', [(int)$minAvgDuration, (int)$maxAvgDuration]);
        }

        // Filter by stops range
        if ($request->has('stops')) {
            [$minStops, $maxStops] = explode('-', $request->input('stops'));
            $query->whereBetween('total_stops', [(int)$minStops, (int)$maxStops]);
        }

        // Filter by flight duration range
        if ($request->has('f_duration')) {
            [$minFDuration, $maxFDuration] = explode('-', $request->input('f_duration'));
            $query->whereBetween('f_duration', [(int)$minFDuration, (int)$maxFDuration]);
        }

        // Filter by total orders range
        if ($request->has('total_orders')) {
            [$minTotalOrders, $maxTotalOrders] = explode('-', $request->input('total_orders'));
            $query->havingRaw('COUNT(*) BETWEEN ? AND ?', [(int)$minTotalOrders, (int)$maxTotalOrders]);
        }

        // Filter by total paid range
        if ($request->has('total_paid')) {
            [$minTotalPaid, $maxTotalPaid] = explode('-', $request->input('total_paid'));
            $query->havingRaw('SUM(paid) BETWEEN ? AND ?', [(int)$minTotalPaid, (int)$maxTotalPaid]);
        }

        // Filter by frequency range
        if ($request->has('frequency')) {
            [$minFrequency, $maxFrequency] = explode('-', $request->input('frequency'));
            $query->havingRaw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) BETWEEN ? AND ?', [(int)$minFrequency, (int)$maxFrequency]);
        }

        // Filter by age range
        if ($request->has('age')) {
            $ageRange = explode('-', $request->input('age'));
            $minAge = $ageRange[0];
            $maxAge = $ageRange[1];
            $query->whereHas('traveler', function ($q) use ($minAge, $maxAge) {
                $q->whereBetween(DB::raw('TIMESTAMPDIFF(YEAR, birth, CURDATE())'), [(int)$minAge, (int)$maxAge]);
            });            
        }

        // Filter by gender
        if ($request->has('gender')) {
            $gender = $request->input('gender');
            $query->whereHas('traveler', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });            
        }

        // Filter by country
        if ($request->has('country')) {
            $country = $request->input('country');
            $query->where('country', $country);
        }
    });

    Log::info('SQL Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

    $users = $query->with(['traveler', 'orders' => function ($q) {
        $q->with(['tour.cities.city', 'tour.natural_destination.natural_destination', 'tour.type.type', 'tour.countries.country']);
    }])->get();

    $result = [];


    foreach ($users as $user) {
        $traveler = $user->traveler; 

        if (!$traveler) {
            continue;
        }

        $orders = $user->orders()->with(['tour.cities.city', 'tour.natural_destination.natural_destination', 'tour.type.type', 'tour.countries.country'])->get();

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

            $orderData = [
                'start' => $order->start,
                'created_at' => $order->created_at,
                'departure' => $order->departure,
                'duration' => $order->duration,
                'tour_length' => $order->tour_length,
                'start_city' => $order->start_city,
                'total_stops' => $order->total_stops,
                'f_duration' => $order->f_duration,
                'tour' => [
                    'cities' => $order->tour->cities->pluck('city')->toArray(),
                    'natural_destinations' => $order->tour->natural_destination->pluck('natural_destination')->toArray(),
                    'types' => $order->tour->type->pluck('type')->toArray(),
                    'countries' => $order->tour->countries->pluck('country')->toArray(),
                ],
            ];

            $ordersData[] = $orderData;
        }

        $result[] = [
            'user' => $user,
            'traveler' => $traveler,
            'totalPaid' => $totalPaid,
            'totalCommission' => $totalCommission,
            'totalDuration' => $totalDuration,
            'totalOrders' => $totalOrders,
            'totalGroupSize' => $totalGroupSize,
            'lastBookingDate' => $lastBookingDate,
            'firstBookingDate' => $firstBookingDate,
            'lastBookingStartCity' => $lastBookingStartCity,
            'orders' => $ordersData,
        ];
    }

    return response()->json($result);
}


=======
    {
        $usersQuery = User::query();

        // Filter by creation date range
        if ($request->has('created_at')) {
            [$startDate, $endDate] = explode('-', $request->input('created_at'));
            $usersQuery->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }
    
        // Fetch users who have orders
        $usersQuery->whereHas('orders', function($query) use ($request) {
            // Filter by departure date range
            if ($request->has('departure')) {
                [$startDate, $endDate] = explode('-', $request->input('departure'));
                $query->whereBetween('departure', [Carbon::parse($startDate), Carbon::parse($endDate)]);
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
                [$minDuration, $maxDuration] = explode('-', $request->input('duration'));
                $query->whereBetween('duration', [(int)$minDuration, (int)$maxDuration]);
            }
    
            // Filter by average duration range
            if ($request->has('average_duration')) {
                [$minAvgDuration, $maxAvgDuration] = explode('-', $request->input('average_duration'));
                $query->havingRaw('AVG(duration) BETWEEN ? AND ?', [(int)$minAvgDuration, (int)$maxAvgDuration]);
            }
    
            // Filter by stops range
            if ($request->has('stops')) {
                [$minStops, $maxStops] = explode('-', $request->input('stops'));
                $query->whereBetween('total_stops', [(int)$minStops, (int)$maxStops]);
            }
    
            // Filter by flight duration range
            if ($request->has('f_duration')) {
                [$minFDuration, $maxFDuration] = explode('-', $request->input('f_duration'));
                $query->whereBetween('f_duration', [(int)$minFDuration, (int)$maxFDuration]);
            }
    
            // Filter by total orders range
            if ($request->has('total_orders')) {
                [$minTotalOrders, $maxTotalOrders] = explode('-', $request->input('total_orders'));
                $query->havingRaw('COUNT(*) BETWEEN ? AND ?', [(int)$minTotalOrders, (int)$maxTotalOrders]);
            }
    
            // Filter by total paid range
            if ($request->has('total_paid')) {
                [$minTotalPaid, $maxTotalPaid] = explode('-', $request->input('total_paid'));
                $query->havingRaw('SUM(paid) BETWEEN ? AND ?', [(int)$minTotalPaid, (int)$maxTotalPaid]);
            }
    
            // Filter by frequency range
            if ($request->has('frequency')) {
                [$minFrequency, $maxFrequency] = explode('-', $request->input('frequency'));
                $query->havingRaw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) BETWEEN ? AND ?', [(int)$minFrequency, (int)$maxFrequency]);
            }
    
            // Filter by age range
            if ($request->has('age')) {
                [$minAge, $maxAge] = explode('-', $request->input('age'));
                $query->whereHas('user', function ($q) use ($minAge, $maxAge) {
                    $q->whereBetween(DB::raw('TIMESTAMPDIFF(YEAR, birth, CURDATE())'), [(int)$minAge, (int)$maxAge]);
                });
            }
    
            // Filter by gender
            if ($request->has('gender')) {
                $gender = $request->input('gender');
                $query->whereHas('user', function ($q) use ($gender) {
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
        });
    
        $users = $usersQuery->get();

        $result = [];

        foreach ($users as $user) {
            $traveler = Traveler::where('mail', $user->email)->first();

            if (!$traveler) {
                continue;
            }

            $orders = $user->orders()->with(['tour.cities.city', 'tour.natural_destination.natural_destination', 'tour.type.type', 'tour.countries.country'])->get();

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

                $orderData = [
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
                    'channel' => $order->channel,
                ];
    
                if ($order->tour) {
                    $orderData['cities'] = $order->tour->cities;
                    $orderData['natural_destination'] = $order->tour->natural_destination;
                    $orderData['type'] = $order->tour->type;
                    $orderData['countries'] = $order->tour->countries;
                }
    
                $ordersData[] = $orderData;
            }

            $result[] = [
                'name' => $traveler->name,
                'country' => $traveler->country,
                'birth' => $traveler->birth,
                'gender' => $traveler->gender,
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
<<<<<<< HEAD
>>>>>>> 5923969 (user orders filters errors corrected)
=======

>>>>>>> parent of 5923969 (user orders filters errors corrected)
}

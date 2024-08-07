<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use Carbon\Carbon;
use DB;

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
    {
        $query = User::query();

        if ($request->has('created_at')) {
            $dates = explode('-', $request->input('created_at'));
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
            $query->whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        if ($request->has('departure')) {
            $dates = explode('-', $request->input('departure'));
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
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
            $query->whereHas('orders', function ($q) use ($frequencies) {
                $q->select('user_id', DB::raw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) as frequency'))
                    ->groupBy('user_id')
                    ->havingBetween('frequency', [trim($frequencies[0]), trim($frequencies[1])]);
            });
        }

        if ($request->has('age')) {
            $ages = explode('-', $request->input('age'));
            $startAge = Carbon::now()->subYears(trim($ages[1]))->format('Y-m-d');
            $endAge = Carbon::now()->subYears(trim($ages[0]))->format('Y-m-d');
            $query->whereHas('travelers', function ($q) use ($startAge, $endAge) {
                $q->whereBetween('birth', [$startAge, $endAge]);
            });
        }

        if ($request->has('gender')) {
            $gender = $request->input('gender');
            $query->whereHas('travelers', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        }

        if ($request->has('country')) {
            $country = $request->input('country');
            $query->whereHas('travelers', function ($q) use ($country) {
                $q->where('country', $country);
            });
        }

        $users = $query->with(['orders.tour.cities.city', 'orders.tour.natural_destination.natural_destination', 'orders.tour.type.type', 'orders.tour.countries.country'])->get();

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
}

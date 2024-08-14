<?php
namespace App\Http\Controllers;

use App\Filters\ContactFilters;
use Illuminate\Http\Request;
use App\Models\User;
use Error;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use App\Mail\ContactMail;
use App\Models\ContactEmail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


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
            'internal_notes'=>$user->internal_notes
        ];

        return response()->json([
            'status' => true,
            'user' => $userData
        ], 200);
    }

    public function Contac(Request $r){
        DB::beginTransaction();
            try{
            $details = [
                'link' => $r->link,
                'order' => $r->order,
                'mail_from'=>$r->mail_from,
                'mail_type' => $r->mail_type,
                'message' => $r->message,
            ];

            $Contact = new ContactEmail();
            $Contact->fill($details)->save();
            Mail::to('adan_gonzalez@vibeadventures.com')->send(new ContactMail($details));
            DB::commit();
            return response()->json(['status'=>200,'response'=>'entro a servicio']);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }

    public function showContac(Request $r){
        try{
            $contact =ContactFilters::ContactE($r);
            return response()->json(['status'=>200, 'count'=>count($contact),'response'=>$contact]);
        }catch(Exception $e){
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }

    public function getUsersWithOrders(Request $request)
    {
        $query = User::query();

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

        $users = $query->get();

        $result = [];

        foreach ($users as $user) {
            $traveler = Traveler::where('user_id', $user->id)->first();

            if (!$traveler) {
                continue;
            }

            $orders = $user->orders()->with(['tour.cities.city', 'tour.natural_destination.natural_destination', 'tour.type.type', 'tour.countries.country'])->get();

            if (!$orders) {
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

                if (!$lastBookingDate || $order->start > $lastBookingDate) {
                    $lastBookingDate = $order->start;
                    $lastBookingStartCity = $order->start_city;
                }

                if (!$firstBookingDate || $order->created_at < $firstBookingDate) {
                    $firstBookingDate = $order->created_at;
                }

                $orderData = [
                    'booking_id' => $order->booking_id,
                    'start' => $order->start,
                    'created_at' => $order->created_at,
                    'departure' => $order->departure,
                    'duration' => $order->duration,
                    'tour_length' => $order->tour_length,
                    'start_city' => $order->start_city,
                    'tour_id' => $order->tour_id,
                    'tour_name' => $order->tour_name,
                    'operator' => $order->operator,
                    'f_duration' => $order->f_duration,
                    'total_stops' => $order->total_stops,
                    'checked_bags' => $order->checked_bags,
                    'paid' => $order->paid,
                    'commission' => $order->commission,
                    'channel' => $order->channel,
                ];

                if ($order->tour) {
                    $orderData['group_size'] = $order->tour->max_group_size;
                    $orderData['cities'] = $order->tour->cities;
                    $orderData['natural_destination'] = $order->tour->natural_destination;
                    $orderData['type'] = $order->tour->type;
                    $orderData['countries'] = $order->tour->countries;
                }

                $totalGroupSize += $orderData['group_size'];

                $totalPaid += $order->paid;
                $totalCommission += $order->commission;
                $totalDuration += $order->duration;

                $ordersData[] = $orderData;

            }

            $groupSizeAverage = $totalOrders > 0 ? $totalGroupSize / $totalOrders : 0;
            $averageCommission = $totalOrders > 0 ? $totalCommission / $totalOrders : 0;
            $grossProfit = $totalOrders > 0 ? $totalPaid * ($totalCommission / $totalOrders) : 0;
            $frequency = $totalOrders > 0 ? $totalOrders / max(Carbon::parse($firstBookingDate)->diffInYears($lastBookingDate), 1) : 0;

            $result[] = [
                'user_id' => $user->id,
                'traveler_id' => $traveler->traveler_id,
                'name' => $traveler->name,
                'country' => $traveler->country,
                'birth' => $traveler->birth,
                'gender' => $traveler->gender,
                'age' => Carbon::parse($traveler->birth)->age,
                'last_booking_start_city' => $lastBookingStartCity,
                'group_size_average' => $groupSizeAverage,
                'last_booking_date' => $lastBookingDate,
                'first_booking_date' => $firstBookingDate,
                'total_paid' => $totalPaid,
                'total_orders' => $totalOrders,
                'total_duration' => $totalDuration,
                'average_duration' => $totalOrders > 0 ? $totalDuration / $totalOrders : 0,
                'average_commission' => $averageCommission,
                'gross_profit' => $grossProfit,
                'frequency' => $frequency,
                'orders' => $ordersData,
            ];
        }

        return ApiResponse::success($result);
    }

    public function editTraveler(Request $r){
        try{
            $user= User::find($r->id);
            $user->fill([
                'hear'=>$r->hear,
                'suscribed'=>$r->suscribed,
                'internal_notes'=>$r->internal_notes,
            ])->save();
            return ApiResponse::success($user);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage(),500);
        }
    }

}

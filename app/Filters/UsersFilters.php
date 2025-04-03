<?php

namespace App\Filters;

use App\Models\ActionLog;
use App\Models\ContactEmail;
use App\Models\Traveler;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
class UsersFilters
{
    protected $notifications;
    protected $permissions;
    protected $permission_text;
    protected $list_days;
    protected $type_list;
    protected $user_order;
    public function __construct(){

        $this->permissions=[
            'inventory'=>false,
            'orders_p'=>false,
            'travelers'=>false,
            'reports'=>false,
            'users'=>false,
            'emails'=>false,
            'actions'=>false,
        ];

        $this->notifications=[
            'orders_n'=>false,
            'cart'=>false,
            'bounced'=>false,
            'report'=>false
        ];

        $this->permission_text=[
            'inventory'=>'Inventory',
            'orders_p'=>'Orders',
            'travelers'=>'Travelers',
            'reports'=>'Reports',
            'users'=>'Settings: Users',
            'emails'=>'Settings: Emails',
            'actions'=>'Settings: Action logs',
        ];

        $this->list_days=[
            1=>Carbon::today(),
            2=>Carbon::yesterdaY(),
            3=>['start'=>Carbon::now()->subDays(7)->startOfDay(),'ends'=>Carbon::now()->endOfDay()],//ultimos 7 dias
            4=>['start'=>Carbon::now()->subDays(30)->startOfDay(),'ends'=>Carbon::now()->endOfDay()],//ultimos 30 dias
            5=>['start'=>Carbon::now()->startOfWeek(),'ends'=>Carbon::now()->endOfWeek()],//semana actual
            6=>['start'=>Carbon::now()->subWeek()->startOfWeek(),'ends'=>Carbon::now()->subWeek()->endOfWeek()],//semana pasada
            7=>['start'=>Carbon::now()->startOfMonth(),'ends'=>Carbon::now()->endOfMonth()],//mes actual
            8=>['start'=>Carbon::now()->subMonth()->startOfMonth(),'ends'=>Carbon::now()->subMonth()->endOfMonth()],//mes pasado
            9=>['start'=>Carbon::now()->startOfYear(),'ends'=>Carbon::now()->endOfYear()],//este año
            10=>['start'=>Carbon::now()->subYear()->startOfYear(),'ends'=>Carbon::now()->subYear()->endOfYear()],//año pasado
        ];

        $this->type_list=[
            1=>'Order',
            2=>'Traveler',
            3=>'Destination',
            4=>'Travel style',
            5=>'Users',
        ];

        $this->user_order=[
            1=>'last_booking_date',
            2=>'last_booking_date',
            3=>'first_booking_date',
            4=>'first_booking_date',
            5=>'total_paid',
            6=>'total_paid',
            7=>'average_duration',
            8=>'average_duration',
            9=>'gross_profit',
            10=>'gross_profit',
            11=>'name',
            12=>'name',
        ];
    }

    public function UsersF(Request $r){

        $admin=$r->admin;
        $filter=$r->filter;
        $users = (new User)->newQuery();

        !$r->name?:$users->where('name', 'like', '%' . $r->name . '%');
        !$r->id?:$users->where('id', $r->id);
        !$r->role?:$users->where('role', $r->role);
        !$r->admin?:$users->select('id', 'name', 'email', 'phone', 'job_id','password','last_login','profile_id','country');
        !$r->limit?:$users->limit($r->limit);
        !$r->filter?:$users->where(function($query)use($filter){
            $query->where('name', 'like', '%' . $filter . '%')
            ->orWhere('email', 'like', '%' . $filter . '%');
        });
        // $users->where('active',1);
        $users = $users->get();

        $users = $users->map(function($u) use($admin) {
            $u->phone = (int) $u->phone;
            $u->job_title=$u->job?$u->job->name:'N/A';
            /* !$admin?:$u->code=$u->password; */
            $permissions =$admin?$this->permissions:[];
            $u->actions = false;
            $notifications =$this->notifications;
            if (!empty($u->permission) && is_iterable($u->permission)) {
                $u->permission->map(function($uu) use (&$permissions, &$notifications,$admin) {
                        $description = $uu->details->description ?? null;

                        if ($description) {
                            if (array_key_exists($description, $this->permissions)) {
                               !$admin?:$permissions[$description] = true;
                               $admin?:array_push($permissions,$description);
                            }
                        if($admin){
                            if (array_key_exists($description, $this->notifications)) {
                                $notifications[$description] = true;
                            }
                        }
                        }
                        unset($uu->details);
                        return $uu;
                    })->values()->all();
                }
                $u->permissions = $permissions?:[];
                if(!$admin){
                    $val=[];
                    foreach( $u->permissions as $p){
                        $val[]=$this->permission_text[$p];
                    }
                    $u->permissions=$val;
                    $admin?:$u->permissions=implode(',',$u->permissions);
                }
                !$admin?:$u->notifications = $notifications;

            unset($u->job);
            unset($u->permission);
            return $u;
        })->values();

        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $users = new LengthAwarePaginator(
            $users->forPage($currentPage, $perPage),
            $users->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );
        return $users;
    }

    public function ActionLogs(Request $r){
        $action= ActionLog::query();
        $name=$r->name;
        $booking_id = $r->input('booking_id');
        $users_id=$r->users_id?explode(',',$r->users_id):[];
        if($name){
            $action->wherehas('user',function($a) use($name) {
                $a->where('name','like',"%{$name}%");
            });
        }
        if($r->dates){
            in_array($r->dates,[1,2])?$action->where('created_at',Carbon::parse($this->list_days[$r->dates])):
            $action->whereBetween('created_at',[Carbon::parse($this->list_days[$r->dates]['start']),Carbon::parse($this->list_days[$r->dates]['ends'])]);
        }
        !count($users_id)>0?:$action->wherein('user_id',$users_id);
        !$r->type?:$action->where('type',$r->type);
        !$r->user_id?:$action->where('user_id',$r->user_id);

        $action->when(!empty($booking_id), function ($query) use ($booking_id) {
            $query->where('booking_id', $booking_id);
        });
        $action = $action->get()->map(function ($actions) {
            $actions->action_date =Carbon::parse($actions->created_at)->format('d M Y, g:i a');
            $actions->email=$actions->user?->email;
            /* $actions->type_name=$this->type_list[$actions->type]; */
            unset($actions->user);
            return $actions;
        })->values();

        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $action = new LengthAwarePaginator(
            $action->forPage($currentPage, $perPage),
            $action->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );

        return $action;
    }


    public function UserWithOrders(Request $r){
        $query = User::query();
        $gross_limit=explode(',',$r->profitSlider);

        if($r->search){
            $query->where(function($q) use ($r){
                $q->where('name','like',"%$r->search%")
                ->orwhere('email','like',"%$r->search%")
                ->orwhere('phone','like',"%$r->search%");
            });
        }

        if ($r->has('created_at')) {
            $dateRange = explode(',', $r->input('created_at'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $query->whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        if ($r->has('departure')) {
            $dates = explode(',', $r->input('departure'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
            $query->whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('departure', [$startDate, $endDate]);
            });
        }

        if ($r->has('cities')) {
            $cities = explode(',', $r->input('cities'));
            $query->whereHas('orders.tour.cities', function ($q) use ($cities) {
                $q->whereIn('t_city_id', $cities);
            });
        }

        if ($r->has('countries')) {
            $countries = explode(',', $r->input('countries'));
            $query->whereHas('orders.tour.countries', function ($q) use ($countries) {
                $q->whereIn('t_country_id', $countries);
            });
        }

        if ($r->has('natural_destinations')) {
            $naturalDestinations = explode(',', $r->input('natural_destinations'));
            $query->whereHas('orders.tour.natural_destination', function ($q) use ($naturalDestinations) {
                $q->whereIn('t_natural_id', $naturalDestinations);
            });
        }

        if ($r->has('types')) {
            $types = explode(',', $r->input('types'));
            $query->whereHas('orders.tour.type', function ($q) use ($types) {
                $q->whereIn('tour_type_id', $types);
            });
        }

        if ($r->has('operators')) {
            $operators = explode(',', $r->input('operators'));
            $query->whereHas('orders', function ($q) use ($operators) {
                $q->whereIn('operator', $operators);
            });
        }

        if ($r->has('duration')) {
            $durations = explode('-', $r->input('duration'));
            $query->whereHas('orders', function ($q) use ($durations) {
                $q->whereBetween('duration', [trim($durations[0]), trim($durations[1])]);
            });
        }

        if ($r->durationavgParam) {
            $averageDurations = explode(',', $r->durationavgParam);
            $query->whereHas('orders', function ($q) use ($averageDurations) {
                $q->select('user_id', DB::raw('AVG(duration) as avg_duration'))
                    ->groupBy('user_id')
                    ->havingBetween('avg_duration', [trim($averageDurations[0]), trim($averageDurations[1])]);
            });
        }

        if ($r->stopsavgParam) {
            $stopRange = explode(',', $r->stopsavgParam);
            $minStops = trim($stopRange[0]);
            $maxStops = trim($stopRange[1]);

            $query->whereHas('orders', function ($q) use ($minStops, $maxStops) {
                $q->select('user_id',DB::raw( 'AVG(total_stops) as avg_stops'))
                  ->groupBy('user_id')
                  ->havingBetween('avg_stops',[$minStops, $maxStops]);
            });
        }

        if ($r->has('stops')) {
            $stops = explode('-', $r->input('stops'));
            $query->whereHas('orders', function ($q) use ($stops) {
                $q->whereBetween('total_stops', [trim($stops[0]), trim($stops[1])]);
            });
        }

        if ($r->has('f_duration')) {
            $fDurations = explode('-', $r->input('f_duration'));
            $query->whereHas('orders', function ($q) use ($fDurations) {
                $q->whereBetween('f_duration', [trim($fDurations[0]), trim($fDurations[1])]);
            });
        }

        if ($r->tripsParam) {
            $totalOrders = explode(',', $r->tripsParam);
            $query->whereHas('orders', function ($q) use ($totalOrders) {
                $q->select('user_id', DB::raw('COUNT(*) as total_orders'))
                    ->groupBy('user_id')
                    ->havingBetween('total_orders', [trim($totalOrders[0]), trim($totalOrders[1])]);
            });
        }

        if ($r->paidParam) {
            $totalPaid = explode(',', $r->paidParam);
            $query->whereHas('orders', function ($q) use ($totalPaid) {
                $q->select('user_id', DB::raw('SUM(paid) as total_paid'))
                    ->groupBy('user_id')
                    ->havingBetween('total_paid', [trim($totalPaid[0]), trim($totalPaid[1])]);
            });
        }

        if ($r->frequencySlider) {
            $frequencies = explode(',', $r->frequencySlider);
            $minFrequency = $frequencies[0];
            $maxFrequency = $frequencies[1];

            $query->whereHas('orders', function ($q) use ($minFrequency, $maxFrequency) {
                $q->select('user_id', DB::raw('COUNT(*) / DATEDIFF(MAX(start), MIN(created_at)) as frequency'))
                    ->groupBy('user_id')
                    ->havingRaw('frequency BETWEEN ? AND ?', [$minFrequency, $maxFrequency]);
            });
        }

        // Filter by age range
        if ($r->ageSlider) {
            $ageRange = explode(',', $r->ageSlider);
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
        if ($r->has('gender')) {
            $gender = $r->input('gender');

            $query->whereHas('orders', function ($q) use ($gender) {
                $q->whereHas('user', function ($q) use ($gender) {
                    $q->whereHas('traveler', function ($q) use ($gender) {
                        $q->wherein('gender', explode(',' ,$gender));
                    });
                });
            });
        }

        // Filter by country
        if ($r->has('country')) {
            $country = $r->input('country');
            $query->whereHas('user', function ($q) use ($country) {
                $q->where('country', $country);
            });
        }

            // Filter by tour IDs
        if ($r->has('tours')) {
            $tourIds = explode(',', $r->input('tours'));
            $query->whereHas('orders.tour', function ($q) use ($tourIds) {
                $q->whereIn('tour_id', $tourIds);
            });
        }

        $users = $query->get();

        $result = [];

        foreach ($users as $user) {
            // Obtén el viajero asociado al usuario
            $traveler = $user->traveler;
            if (!$traveler) {
                continue;
            }


            $orders = $user->orders()
                ->with([
                    'tour.cities.city',
                    'tour.natural_destination.natural_destination',
                    'tour.type.type',
                    'tour.countries.country'
                ])
                ->get();

            if ($orders->isEmpty()) {
                continue;
            }


            $totalPaid =floor($orders->sum('paid'));
            $totalCommission = $orders->sum('commission');
            $totalDuration = $orders->sum('duration');
            $totalOrders = $orders->count();
            $totalGroupSize = $orders->sum(fn($order) => $order->tour->max_group_size ?? 0);
            $lastBookingDate = $orders->max('start');
            $firstBookingDate = $orders->min('created_at');

            $ordersData = $orders->map(function ($order) {
                return [
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
                    'group_size' => $order->tour->max_group_size ?? 0,
                    'cities' => $order->tour->cities ?? [],
                    'natural_destination' => $order->tour->natural_destination ?? [],
                    'type' => $order->tour->type ?? [],
                    'countries' => $order->tour->countries ?? [],
                ];
            })->toArray();

            $grossProfit = $totalPaid * ($totalCommission / max($totalOrders, 1));
            $groupSizeAverage = $totalOrders > 0 ? $totalGroupSize / $totalOrders : 0;
            $frequency = $totalOrders > 0
                ? $totalOrders / max(Carbon::parse($firstBookingDate)->diffInYears($lastBookingDate), 1)
                : 0;


            if (!((float)$grossProfit >= (float)$gross_limit[0] && (float)$grossProfit <= (float)$gross_limit[1])) {
                continue;
            }

            $result[] = [
                'user_id' => $user->id,
                'traveler_id' => $traveler->traveler_id,
                'name' => $traveler->name,
                'country' => $traveler->country,
                'birth' => $traveler->birth,
                'gender' => $traveler->gender,
                'age' => Carbon::parse($traveler->birth)->age,
                'last_booking_start_city' => $orders->last()?->start_city,
                'group_size_average' => round($groupSizeAverage),
                'last_booking_date' => $lastBookingDate,
                'first_booking_date' => $firstBookingDate,
                'total_paid' => number_format($totalPaid, 2),
                'total_orders' => $totalOrders,
                'total_duration' => $totalDuration,
                'average_duration' => $totalOrders > 0 ? $totalDuration / $totalOrders : 0,
                'average_commission' => $totalCommission / max($totalOrders, 1),
                'gross_profit' => number_format($grossProfit, 2),
                'frequency' => $frequency,
                'orders' => $ordersData,
            ];
        }


        if($r->order_by){
            usort($result, function ($a, $b) use ($r) {
                $column = $this->user_order[$r->order_by];
                if(in_array($r->order_by,[1,2,3,4])){
                    if ($r->order_by % 2 == 0) {
                        return strtotime($a[$column]) <=> strtotime($b[$column]);}
                        else {
                            return strtotime($b[$column]) <=> strtotime($a[$column]);
                        }
                    }
                elseif(in_array($r->order_by,[5,6,7,8,9,10])){
                    if ($r->order_by % 2 == 0) {
                        return floatval(str_replace(',', '', $a[$column])) <=> floatval(str_replace(',', '', $b[$column]));
                    } else {
                        return floatval(str_replace(',', '', $b[$column])) <=> floatval(str_replace(',', '', $a[$column]));
                    }
                    }else{
                        if ($r->order_by % 2 == 0) {
                            return $a[$column] <=> $b[$column];
                        }else {
                                return $b[$column] <=> $a[$column];
                            }
                    }

                });
            }


        $users= collect($result);
        if(!$r->csv){
            $currentPage =$r->page?$r->page:1;
            $perPage = $r->per_page? $r->per_page:10;

            $users = new LengthAwarePaginator(
                $users->forPage($currentPage, $perPage),
                $users->count(),
                $perPage,
                $currentPage,
                ['path' => $r->url(), 'query' => $r->query()]
            );
        }


        return $users;
    }
}

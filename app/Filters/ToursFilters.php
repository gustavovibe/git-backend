<?php

namespace App\Filters;

use App\Models\Order;
use App\Models\Tour;
use App\Models\Type;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ToursFilters
{

    protected $list_days;
    protected $list_whole_trip;
    protected $list_age_group;
    protected $list_hours;
    protected $orderby;
    public function __construct()
    {
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

        $this->list_whole_trip=[
            1=>[1,3],
            2=>[4,10],
            3=>[11,15],
            4=>[16,20],
            5=>[21,25],
            6=>[26,30],
            7=>[31],
        ];

        $this->list_age_group =[
            1=>[1,2],
            2=>[3,5],
            3=>[6,10],
            4=>[11,15],
            5=>[16,20],
            6=>[21],
        ];

        $this->list_hours=[
            1=>[0,4],
            2=>[5,8],
            3=>[9,12],
            4=>[13,16],
            5=>[17,20],
            6=>[21,24],
        ];

        $this->orderby = [
            1 => 'created_at',
            2 => 'created_at',
            3 => 'start',
            4 => 'start',
            5 => 'paid',
            6 => 'paid',
            7 => 'average_price_per_person_per_day', // Campo calculado
            8 => 'average_price_per_person_per_day', // Campo calculado
            9 => 'gross_profit_ratio',              // Campo calculado
            10 => 'gross_profit_ratio',             // Campo calculado
        ];

    }

    public function getListDays()
    {
        return $this->list_days;
    }

    public function getListWholeTrips()
    {
       return $this->list_whole_trip;
    }

    public function getListAges(){
        return $this->list_age_group;
    }

    public function getListHours(){
        return $this->list_hours;
    }


    public static function ToursP(Request $r){
    $tour_type = $r->tour_type ?: 0;
    $city = $r->city ? explode(',', $r->city) : [];
    $country = $r->country;
    $order= explode(',',$r->order);
    $minRange = $maxRange = null;
    [$minRange, $maxRange] = array_map('intval', explode(',', $r->range));
    $val_list=[
        1=>'tour_name',
        2=>'max_group_size',
        3=>'orders_count',
        4=>'commission',
        5=>'total_commision',
        6=>'price_total',
    ];
    $tourQuery = Tour::query();

    !$r->id?:$tourQuery->where('tour_id', $r->id);

    !$r->tour_type?:$tourQuery->whereHas('type', function ($q) use ($tour_type) {
            $q->whereIn('tour_type_id', [$tour_type]);
        });

    !$r->city?:$tourQuery->whereHas('cities', function ($q) use ($city) {
            $q->whereIn('t_city_id', $city);
        });


    !$r->country?:$tourQuery->whereHas('country', function ($q) use ($country) {
            $q->whereIn('t_country_id', $country);
        });


    if ($r->commission) {
        $commission = explode(',', $r->commission);
        $minCommission = (double)$commission[0];
        $maxCommission = (double)$commission[1];

        if ($minCommission == 0) {
            $tourQuery->where(function($query) use($minCommission, $maxCommission) {
                $query->whereDoesntHave('orders')
                      ->orWhereHas('orders', function($q) use($minCommission, $maxCommission) {
                          $q->whereBetween('commission', [$minCommission, $maxCommission]);
                      });
            });
        }elseif($minCommission == $maxCommission){
            if($minCommission==0){
                $tourQuery->where(function($query)  {
                    $query->whereDoesntHave('orders');
                });
            }else{
                $tourQuery->where(function($query) use($minCommission) {
                    $query->whereHas('orders', function($q) use($minCommission) {
                              $q->where('commission','like',"%{$minCommission}%");
                          });
                });
            }
        }
        else {
            $tourQuery->whereHas('orders', function($q) use($minCommission, $maxCommission) {
                $q->whereBetween('commission', [$minCommission, $maxCommission]);
            });
        }
    }

    !$r->tour_name?:$tourQuery->where('tour_name', 'like', "%{$r->tour_name}%");
    $tourQuery->select('tour_name', 'end_city', 'departures', 'max_group_size', 'commission', 'price_total', 'tour_id', 'operator_id')->with(['orders', 'natural_destination', 'type', 'cities'])->withCount('orders');

    if(!in_array((int)$order[0],[4,5,6])){
        $tourQuery->orderby($val_list[(int)$order[0]],(int)$order[1]==1?'Asc':'Desc');
    }

    $paginator =$tourQuery->get();

    $paginator = $paginator->map(function ($t)  {

        $t->travel_style = $t->type->map(function($tt) {
            return $tt->type->tourtype_name;
        });

        $t->cities_tour = $t->cities->map(function($tt) {
            return $tt->city->city_name;
        });

        $t->comision = 0;
        $t->price_total = 0;
        $list_c = [];

        foreach ($t->orders as $o) {
            $t->comision += $o->commission * $o->paid;
            $t->price_total += $o->paid;
            $commissionPercentage = $o->commission * 100;
            if (!in_array($commissionPercentage, $list_c)) {
                $list_c[] = $commissionPercentage;
            }
        }

        $t->commission =count($list_c)?implode(',', $list_c):'0';
        $t->total_commision =  $t->comision;
        $t->price_total = $t->price_total;

        return $t->makeHidden(['cities', 'type', 'city', 'orders']);
    })->filter(function($t) use ($minRange, $maxRange) {
        return $t->price_total >= $minRange && $t->price_total <= $maxRange;
    })->values();

    if (in_array((int)$order[0], [4, 5, 6])) {
        $paginator = $paginator->sort(function ($a, $b) use ($order, $val_list) {
            $field = $val_list[(int)$order[0]];

            if ((int)$order[1] == 1) {
                return (double)$a->{$field} <=> (double)$b->{$field};
            } else {
                return (double)$b->{$field} <=> (double)$a->{$field};
            }
        })->values();
    }

    $paginator = $paginator instanceof Collection ? $paginator : collect($paginator);
    $perPage = $r->limit ?: 15;
    $currentPage = $r->page ?: 1;
    $paginator = new LengthAwarePaginator(
        $paginator->forPage($currentPage, $perPage),
        $paginator->count(),
        $perPage,
        $currentPage,
        ['path' => $r->url()]
    );


    return $paginator;
}




    public function travel_styles(Request $r) {
        $travel = Type::query();
        $orderby = $r->order;
        $commission = explode(',', $r->commission);
        $range = explode(',', $r->range);
        $minRange = (int) $range[0];
        $maxRange = (int) $range[1];
        $minCommission = (double)$commission[0];
        $maxCommission = (double)$commission[1];

        !$r->name ?: $travel->where('tourtype_name', 'like', "%{$r->name}%");
        !$r->id ?: $travel->where('tour_type_id', $r->id);
        if (in_array($orderby, [1, 2])) {
            $orderby == 1 ? $travel->orderBy('tourtype_name', 'ASC') : $travel->orderBy('tourtype_name', 'DESC');
        }

        $filteredTourIds = Order::query()
            ->select('tour_id')
            ->selectRaw('SUM(paid) as total_paid')
            ->whereBetween('commission', [$minCommission, $maxCommission])
            ->groupBy('tour_id')
            ->havingRaw('SUM(paid) BETWEEN ? AND ?', [$minRange, $maxRange])
            ->pluck('tour_id')
            ->toArray();

        $minCommission == 0 ?: $travel->whereHas('type_t', function ($query) use ($filteredTourIds) {
            $query->whereIn('tour_id', $filteredTourIds);
        });

        $travel = $travel->with('type_t:tour_type_id,tour_id')->withCount('type_t')->get();

        // Filtrar resultados
        $paginator = $travel->filter(function ($tra) use ($filteredTourIds, $minCommission, $maxCommission, $minRange, $maxRange) {
            $tra->comission_range = [];
            $tra->comission_total = 0;
            $tra->total_paid = 0;
            $tra->type_ids = $tra->type_t->pluck('tour_id')->toArray();
            $iguales = array_intersect($filteredTourIds, $tra->type_ids);
            $order = Order::select('tour_id', 'paid', 'commission')->whereIn('tour_id', $iguales)->get();

            $comission_range = [];
            foreach ($order as $or) {
                if ($or->commission >= $minCommission && $or->commission <= $maxCommission) {
                    $tra->total_paid += (double)$or->paid;
                    $tra->comission_total += ((double)$or->paid * $or->commission);
                    $commissionPercentage = 100 * $or->commission;
                    if (!in_array($commissionPercentage, $comission_range)) {
                        $comission_range[] = $commissionPercentage;
                    }
                }
            }

            if ($tra->total_paid >= $minRange && $tra->total_paid <= $maxRange) {
                $tra->comission_range = count($comission_range) > 0 ? implode(',', $comission_range) : '0';
                $tra->comission_total = round($tra->comission_total, 2);
                $tra->total_paid = round($tra->total_paid, 2);
                unset($tra->order);
                unset($tra->type_ids);
                unset($tra->type_t);
                return true;
            }

            return false;
        })->values();


        $val_list = [
            3 => 'total_paid',
            4 => 'total_paid',
            7 => 'type_t_count',
            8 => 'type_t_count',
        ];

        if (in_array((int)$orderby, [3, 4, 5, 6, 7, 8])) {
            $paginator= $paginator->sort(function ($a, $b) use ($orderby, $val_list) {
                if (in_array($orderby, [3, 5, 7])) {
                    return (double)$a->{$val_list[$orderby]} <=> (double)$b->{$val_list[$orderby]};
                }
                if (in_array($orderby, [4, 6, 8])) {
                    return (double)$b->{$val_list[$orderby]} <=> (double)$a->{$val_list[$orderby]};
                }
            })->values();
        }

        $paginator = $paginator instanceof Collection ? $paginator : collect($paginator);
        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $paginator = new LengthAwarePaginator(
            $paginator->forPage($currentPage, $perPage),
            $paginator->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );

        return $paginator;
    }





    public function destinations(Request $r){
        $destination= City::query();
        $orderby=(int)$r->order;
        $commission=explode(',',$r->commission);
        $range=explode(',',$r->range);
        $cities=$r->id_cities?explode(',',$r->id_cities):'';
        $countries=$r->id_countries?explode(',',$r->id_countries):'';
        $destinations=$r->id_destinations?explode(',',$r->id_destinations):'';
        $minRange =(int) $range[0];
        $maxRange =(int) $range[1];
        $minCommission = (double)$commission[0];
        $maxCommission = (double)$commission[1];
        !$r->city_name?:$destination->where('city_name','like',"%{$r->city_name}%");
        !$r->t_city_id?:$destination->where('t_city_id',$r->t_city_id);

        $destination->whereHas('tours', function ($query) {
            $query->whereHas('orders');
        });

        !$cities?:$destination->whereHas('tours', function($query) use ($cities) {
            $query->whereIn('t_city_id', $cities);
        });

        if (!empty($destinations)) {
            $destination->whereHas('tours', function($query) use ($destinations) {
                $query->whereHas('tour', function($query) use ($destinations) {
                    $query->whereHas('natural_destination', function($query) use ($destinations) {
                        $query->whereIn('t_natural_id', $destinations);
                    });
                });
            });
        }


        !$r->limit?:$destination->limit($r->limit);

        if(in_array($orderby,[1,2])){
            $orderby==1?$destination->orderBy('city_name', 'ASC'):$destination->orderBy('city_name', 'DESC');
        }


        $destination = $destination->withCount('tours')->get()->map(function($des) use($minCommission, $maxCommission){
            $des->tour_ids = $des->tours->map(function($tour) {
                return $tour->tour_id;
            })->values()->toArray();

            $orders = Order::whereIn('tour_id', $des->tour_ids) ->whereBetween('commission', [$minCommission, $maxCommission])->select('tour_id', 'paid', 'commission')->get();

            $des->total_paid_commission = 0;
            $des->total_paid = 0;

            $commision_r = [];
            foreach ($orders as $order) {
                $des->total_paid_commission += $order->commission * (double)$order->paid;
                $des->total_paid += (double)$order->paid;
                if (!in_array(100 * $order->commission, $commision_r)) {
                    $commision_r[] = 100 * $order->commission;
                }
            }

            $des->commission_r = count($commision_r) ? implode(',', $commision_r) : '0';
            $des->commission_a =$commision_r;
            $commision_r = [];
            $des->total_paid_commission = round($des->total_paid_commission, 2);
            $des->total_paid = round($des->total_paid, 2);

            unset($des->tour_ids);
            unset($des->tours);
            return $des;
        })->filter(function($des) use ($minRange, $maxRange) {
            return ($des->total_paid >= $minRange && $des->total_paid <= $maxRange);

        })->values()->all();

        $val_list=[
            3=>'total_paid',
            4=>'total_paid',
            7=>'tours_count',
            8=>'tours_count',
        ];
        if ( in_array($orderby,[3,4,5,6,7,8])) {
            usort($destination, function ($a, $b) use($orderby,$val_list){
                if (in_array($orderby, [3, 5, 7])) {
                    return $a->{$val_list[$orderby]} <=> $b->{$val_list[$orderby]};
                }
                if (in_array($orderby, [4, 6, 8])) {
                    return $b->{$val_list[$orderby]} <=> $a->{$val_list[$orderby]};
                }
            });
        }

        return $destination;
    }

    public static function OrdersPrint(Request $r) {
				
        $orders = Order::with(['travelers', 'user', 'tour'])->where('booking_id', $r->tour_id)->first();
				
				if(!$orders){
					return null;
				}
        if ($orders->start && $orders->end) {
          $orders->days = Carbon::parse($orders->start)->diffInDays(Carbon::parse($orders->end));
        }
    
        if ($orders->tour) {
            $orders->image = $orders->tour->main_image;
            $orders->reviews_count = $orders->tour->reviews_count;
            $orders->ratings_overall = $orders->tour->ratings_overall;
            unset($orders->tour);
        }
    
        return $orders;
    }
    

    public function OrdersAll(Request $r,$csv){

        $orders = Order::query();
        $orders->with(['flightTour', 'travelers', 'user','natural_destination']);
        !$r->booking_id?:$orders->where('booking_id',$r->booking_id);

        //dates (booking=created_at)
        $orders->where(function ($query) use ($r){

            if($r->booking){
                in_array($r->booking,[1,2])?$query->orWhere('created_at',Carbon::parse($this->list_days[$r->booking])):
                $query->orWhereBetween('created_at',[Carbon::parse($this->list_days[$r->booking]['start']),Carbon::parse($this->list_days[$r->booking]['ends'])]);
            }

            if($r->travel){
                in_array($r->travel,[1,2])?$query->orWhere('start',Carbon::parse($this->list_days[$r->travel])):
                $query->orWhereBetween('start',[Carbon::parse($this->list_days[$r->travel]['start']),Carbon::parse($this->list_days[$r->travel]['ends'])]);
            }
        });


        //category (travel_style=type)
        !$r->operator?:$orders->wherein('operator',explode(',', $r->operator));

        if ($r->travel_style) {
            $orders->whereHas('tour.type', function ($query) use ($r) {
                $query->wherein('tour_type_id', explode(',',$r->travel_style));
            });
        }

        if( $r->destination_city){
            $orders->whereHas('tour.cities',function($query) use ($r){
                $query->whereIn('t_city_id',explode(',',$r->destination));
            });
        }

        if( $r->destination_country){
            $orders->whereHas('tour.countries',function($query) use ($r){
                $query->whereIn('t_country_id',explode(',',$r->destination));
            });
        }

        //adventure
        !$r->adventure?:$orders->wherein('tour_id',explode(',', $r->adventure));
        !$r->status?:$orders->wherein('tourradar_status',explode(',', $r->status));

        if($r->whole_trip){
            $value=$this->list_whole_trip[$r->whole_trip];
            $r->whole_trip!=7?$orders->WhereBetween('whole_trip',[$value[0],$value[1]]) :$orders->where('whole_trip','>=',$value[0]);
        }

        if ($r->duration) {
            $val_d= $this->list_whole_trip[$r->duration];
            $orders->whereHas('tour', function ($query) use ($val_d, $r) {
             $r->duration==7?$query->where('tour_length_days','>=',31):$query->whereBetween('tour_length_days',[$val_d[0],$val_d[1]]);

            });
        }

        !$r->carrier?:$orders->wherein('carrier',explode(',', $r->carrier));

        //traveler
        if($r->age_group){
            $val_age= $this->list_age_group[$r->age_group];
            $r->age_group==6?$orders->where('age_group','>=',$val_age[0]):$orders->whereBetween('age_group',[$val_age[0],$val_age[1]]);
        }

        if($r->group_size){
             $r->group_size<11?$orders->wherein('group_size',explode(',', $r->group_size)):$orders->where('group_size','>=',11);
        }

        !$r->gender?:$orders->wherein('gender',explode(',', $r->gender));
        !$r->country?:$orders->wherein('country',explode(',', $r->country));

        //booking
        !$r->channel?:$orders->wherein('channel',explode(',', $r->channel));
        !$r->payment_method?:$orders->wherein('payment_method',explode(',', $r->payment_method));
        !$r->medium?:$orders->wherein('medium',explode(',', $r->medium));
        !$r->day?:$orders->wherein(DB::raw('DAYOFWEEK(start)'),explode(',',$r->day));

        if ($r->hour) {
            $hours = $this->list_hours[$r->hour];
            $orders->whereBetween(DB::raw('HOUR(created_at)'), $hours);
        }


        if ($r->sort_by && (int)$r->sort_by <= 10) {
            $direction = ($r->sort_by % 2 == 0) ? 'desc' : 'asc';

            if (in_array($r->sort_by, [1, 2, 3, 4, 5, 6])) {
                $orders->orderBy($this->orderby[$r->sort_by], $direction);
            }
        }

        $orders = $orders->get();

        //calculados
        if (in_array($r->sort_by, [7, 8, 9, 10])) {
            $sortField = $this->orderby[$r->sort_by];

            $orders = $orders->sortBy(function ($order) use ($sortField) {
                return $order->$sortField;
            }, SORT_REGULAR, $direction === 'desc')->values();
        }


        $orders= $orders->map(function ($order) {
            $order->paid = floor($order->paid);
            $order->grossProfit =  number_format($order->grossProfit,2) ;
            $order->averagePricePerPersonPerDay = number_format($order->average_price_per_person_per_day,2) ;
            $order->gross_profit_ratio = $order->gross_profit_ratio.'%';
            return $order;
        })->all();

        if($csv){
            return $orders;
        }
        $orders = collect($orders);
        $perPage = $r->limit?$r->limit:15;
        $page = $r->page?$r->page:1;
        $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator(
            $orders->forPage($page, $perPage),
            $orders->count(),
            $perPage,
            $page,
            ['path' => $r->url()]
        );

        return $paginatedOrders;
    }


}

<?php

namespace App\Filters;

use App\Models\Order;
use App\Models\Tour;
use App\Models\Type;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
class ToursFilters
{
    public function ToursP(Request $r){
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
        $filtered = $travel->filter(function ($tra) use ($filteredTourIds, $minCommission, $maxCommission, $minRange, $maxRange) {
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
        });

        // Aplicar la paginación después del filtrado
        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($currentPage, $perPage),
            $filtered->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );

        // Ordenar si es necesario
        $val_list = [
            3 => 'total_paid',
            4 => 'total_paid',
            7 => 'type_t_count',
            8 => 'type_t_count',
        ];

        if (in_array($orderby, [3, 4, 5, 6, 7, 8])) {
            $paginated->getCollection()->sort(function ($a, $b) use ($orderby, $val_list) {
                if (in_array($orderby, [3, 5, 7])) {
                    return $a->{$val_list[$orderby]} <=> $b->{$val_list[$orderby]};
                }
                if (in_array($orderby, [4, 6, 8])) {
                    return $b->{$val_list[$orderby]} <=> $a->{$val_list[$orderby]};
                }
            });
        }

        return $paginated;
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
}

<?php

namespace App\Filters;

use App\Models\City;
use App\Models\Country;
use App\Models\Operators;
use App\Models\Order;
use App\Models\Tour;
use App\Models\TourCountry;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
class OperatorsFilters
{

    public static function OperatorsF(Request $r) {

        $minRange = (int) explode(',', $r->range)[0];
        $maxRange = (int) explode(',', $r->range)[1];
        $query = Operators::query();
        $orderby = $r->order;
        $city = $r->city ? explode(',', $r->city) : [];
        $name = $r->name;
        $operatorId = $r->operator_id;

        !$r->id?:$query->where('id',$r->id);
        !$name?:$query->where('name', 'like', "%{$name}%");
        !$operatorId?:$query->where('operator_id', $operatorId);


        if (count($city)) {
            $query->wherehas('tours', function ($query) use ($city) {
                $query->whereHas('cities', function ($query) use ($city) {
                    $query->whereIn('t_city_id', $city);
                });
            });
        }




        if ($r->commission) {
            $commission = explode(',', $r->commission);
            $minCommission = (double)$commission[0];
            $maxCommission = (double)$commission[1];

            if ($minCommission == 0) {
                $query->where(function($query) use($minCommission, $maxCommission) {
                    $query->whereDoesntHave('orders')
                          ->orWhereHas('orders', function($q) use($minCommission, $maxCommission) {
                              $q->whereBetween('commission', [$minCommission, $maxCommission]);
                          });
                });
            }elseif($minCommission == $maxCommission){
                if($minCommission==0){
                    $query->where(function($query)  {
                        $query->whereDoesntHave('orders');
                    });
                }else{
                    $query->where(function($query) use($minCommission) {
                        $query->whereHas('orders', function($q) use($minCommission) {
                                  $q->where('commission','like',"%{$minCommission}%");
                              });
                    });
                }
            }
            else {
                $query->whereHas('orders', function($q) use($minCommission, $maxCommission) {
                    $q->whereBetween('commission', [$minCommission, $maxCommission]);
                });
            }
        }

        if (in_array($orderby, [1, 2])) {
        $query->orderBy('name', $orderby == 1 ? 'ASC' : 'DESC');
        }

        if (in_array($orderby, [7, 8])) {
            $query->orderBy('tours_count', $orderby == 7 ? 'ASC' : 'DESC');
        }

        $query->with([
            'tours:tour_id,operator_id,commission,price_total,max_group_size',
            'tours.countries:t_country_id,tour_id',
        ])->withCount('tours')->with('orders')->withCount('orders');

        $results = $query->get();

        $filtered = $results->map(function($op){
            $countries = [];

            $op->total_paid = 0;
            $op->total_paid_2 = 0;
            $op->total_commission = 0;
            $totalGroupSize = 0;
            $op->total_paid_commission = 0;
            $comission_r = [];

            foreach ($op->tours as $tour) {
                foreach ($tour->countries as $country) {
                    if (!in_array($country->t_country_id, $countries)) {
                        $countries[] = $country->t_country_id;
                    }
                }
                $totalGroupSize += $tour->max_group_size;

            }
            $total_comission=0;
            $orders=Order::select('operator','paid','commission')->where('operator',$op->operator_id)->get();
            if(count($orders)){
                foreach ($orders as $order) {
                    $paid = (double) $order->paid;
                    $commission = (double) $order->commission;
                    $total_comission += $commission * $paid;
                    $op->total_paid += $paid;
                   if (!in_array((100 * $commission), $comission_r)) {
                        $comission_r[] = 100 * $commission;
                    }
                }
            }

            $op->total_paid_commission=$total_comission;

            $op->comission_r = count($comission_r) ? implode(',', $comission_r) : '0';
            $op->countries_name = Country::whereIn('t_country_id', $countries)->pluck('name');
            $op->total_paid_commission = round($op->total_paid_commission, 2);
            $op->total_paid = round($op->total_paid, 2);

            $op->average_size_group = $op->tours_count > 0 ? round($totalGroupSize / $op->tours_count) : 0;
            $op->countries_t = count($countries);
            unset($op->orders);
            unset($op->tours);
            return $op;
        })->filter(function($op) use ($minRange,$maxRange) {
            return ($op->total_paid >= $minRange && $op->total_paid<= $maxRange);
        })->values();

        // Ordenar resultados
        $val_list = [
            3 => 'total_paid',
            4 => 'total_paid',
            5 => 'total_paid_commission',
            6 => 'total_paid_commission',
        ];

        if (in_array($orderby, [3, 4, 5, 6])) {
            $filtered = $filtered->sortBy(function ($item) use ($orderby, $val_list) {
                return $item->{$val_list[$orderby]};
            }, SORT_REGULAR, in_array($orderby, [4, 6]));

            // Convertir el resultado en una colección si no lo es ya
            $filtered = $filtered->values();
        }

        $page = $r->page ?: 1;
        $limit = $r->limit ?: 15;
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $limit),
            $filtered->count(),
            $limit,
            $page,
            ['path' => $r->url()]
        );

        return $paginated;
    }
}

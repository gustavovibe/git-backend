<?php

namespace App\Filters;

use App\Models\City;
use App\Models\Country;
use App\Models\Operators;
use App\Models\Tour;
use App\Models\TourCountry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OperatorsFilters
{

    public function OperatorsF (Request $r){
        $operator = Operators::query();
        $orderby=$r->order;
        !$r->name?:$operator->where('name','like',"%{$r->name}%");
        !$r->operator_id?:$operator->where('operator_id',$r->operator_id);
        if(in_array($orderby,[1,2])){
            $orderby==1?$operator->orderBy('name', 'ASC'):$operator->orderBy('name', 'DESC');
        }
        $commission=explode(',',$r->commission);
        $range=explode(',',$r->range);

        $minRange =(int) $range[0];
        $maxRange =(int) $range[1];
        $minCommission = (double)$commission[0];
        $maxCommission = (double)$commission[1];

        $operator = $operator->with([
            'tours:tour_id,operator_id,commission,price_total,max_group_size',
            'tours.countries:t_country_id,tour_id',
            'orders:operator,paid,commission'
        ])->withCount('tours')->get()->map(function($op) use($minCommission, $maxCommission){
            $countries = [];
            $op->total_paid = 0;
            $op->total_paid_2 = 0;
            $op->total_commission = 0;
            $totalGroupSize = 0;
            $op->total_paid_commission = 0;


            foreach ($op->tours as $tour) {
                foreach ($tour->countries as $country) {
                    if (!in_array($country->t_country_id, $countries)) {
                        $countries[] = $country->t_country_id;
                    }
                }
                $totalGroupSize += $tour->max_group_size;
            }

            foreach ($op->orders as $order) {
                if ($order->commission >= $minCommission && $order->commission <= $maxCommission) {
                    $op->total_paid_commission += $order->commission * (double)$order->paid;
                    $op->total_paid += (double)$order->paid;
                }
            }

            $text=Country::wherein('t_country_id',$countries)->select('name','t_country_id')->get()->map(function($t){
              return $t->name;
            });

            $op->countries_name=$text;
            $op->total_paid_commission = round($op->total_paid_commission, 2);
            $op->total_paid = round($op->total_paid, 2);
            $op->total_paid_2 = round($op->total_paid, 2);
            $op->average_size_group = $op->tours_count > 0 ?  round($totalGroupSize / $op->tours_count) : 0;
            $op->countries_t = count($countries);
            unset($op->tours);
            return $op;
        })->filter(function($op) use ($minRange, $maxRange) {
            return $op->total_paid_2 >= $minRange && $op->total_paid_2 <= $maxRange;
        })->values()->all();

        $val_list=[
            3=>'total_paid_2',
            4=>'total_paid_2',
            5=>'total_paid_commission',
            6=>'total_paid_commission',
            7=>'tours_count',
            8=>'tours_count',
        ];
        if ( in_array($orderby,[3,4,5,6,7,8])) {
            usort($operator, function ($a, $b) use($orderby,$val_list){
                if (in_array($orderby, [3, 5, 7])) {
                    return $a->{$val_list[$orderby]} <=> $b->{$val_list[$orderby]};
                }
                if (in_array($orderby, [4, 6, 8])) {
                    return $b->{$val_list[$orderby]} <=> $a->{$val_list[$orderby]};
                }
            });
        }

        return $operator;
    }
}

<?php

namespace App\Filters;

use App\Models\City;
use App\Models\ContactEmail;
use App\Models\Order;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ToursFilters
{
    public function ToursP(Request $r){
        $tour_type=$r->tour_type?$r->tour_type:0;
        $city=$r->city?explode(',',$r->city)  :[];
        $country=$r->country;
        $admin=$r->admin;

        $tour= Tour::query();

        !$r->tour_type?:$tour->WhereHas('type', function ($q) use ($tour_type) {
            $q->whereIn('tour_type_id',[ $tour_type]);
        });

        !$r->city?:$tour->WhereHas('cities', function ($q) use ($city) {
            $q->whereIn('t_city_id',$city);
        });

        !$r->country?:$tour->WhereHas('country', function ($q) use ($country) {
            $q->whereIn('t_country_id',$country);
        });

        if($r->commission){
            $commission=explode(',',$r->commission);
            $com=(double)$commission[0];
            $commission[0]==$commission[1]?$tour->where('commission','like',"%{$com}%"):$tour->whereBetween('commission',[(double)$commission[0],(double)$commission[1]]);
        }

        if($r->range){
            $range=explode(',',$r->range);
            $range[0]==$range[1]?$tour->where('price_total',(double)$range[0]):$tour->whereBetween('price_total',[(double)$range[0],(double)$range[1]]);
        }

        !$r->id?:$tour->where('tour_id',$r->id);
        !$r->tour_name?:$tour->where('tour_name','like',"%{$r->tour_name}%");
        !$r->admin?:$tour->select('tour_name','end_city','departures','max_group_size','commission','price_total','tour_id','operator_id');
        !$r->limit?:$tour->limit($r->limit);
        $tour=$tour->with('natural_destination')->with('type')->with('cities')->get();
        $tour=$tour->map(function($t) use($admin){
            if($admin){
                $this->entro1=[];
                $this->entro2=[];
                $t->type=$t->type->map(function($tt) {
                    $this->entro1[]=$tt->type->tourtype_name;
                    return $tt;
                })->values()->all();
                $t->cities=$t->cities->map(function($tt) {
                    $this->entro2[]=$tt->city->city_name;
                    return $tt;
                })->values()->all();
                $t->travel_style=$this->entro1;
                $t->cities_tour=$this->entro2;
            }
            $t->city_name=$t->city->city_name;
            $t->comision=((double)$t->price_total)*$t->commission;
            $t->total_commision='$'.$t->comision;
            $t->commission=($t->commission*100).'%';
            $t->price_total='$'.$t->price_total;
            $t->sent='https://api.sandbox.b2b.tourradar.com/v1/operators/'.$t->operator_id;
            unset($t->cities);
            unset($t->type);
            unset($t->city);
            return $t;
        })->values()->all();
        return $tour;
    }

    public function destinations(Request $r){
        $destination= City::query();
        $orderby=$r->order;
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

        !$destinations?:$destination->whereHas('tours', function($query) use ($countries) {
            $query->whereHas('tour', function($query) use ($countries) {
                $query->whereHas('countries', function($query) use ($countries) {
                    $query->whereIn('t_country_id', $countries);
                });
            });
        });

        !$destinations?:$destination->whereHas('tours', function($query) use ($destinations) {
            $query->whereHas('tour', function($query) use ($destinations) {
                $query->whereHas('natural_destination', function($query) use ($destinations) {
                    $query->whereIn('t_natural_id', $destinations);
                });
            });
        });

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

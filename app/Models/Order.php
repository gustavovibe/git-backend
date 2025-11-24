<?php

namespace App\Models;

use App\Filters\ToursFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'departure',
        'start',
        'arrival',
        'end',
        'duration',
        'tour_length',
        'tour_name',
        'tour_id',
        'style',
        'operator',
        'start_city',
        'end_city',
        'booking_status',
        'duffel_status',
        'tourradar_id',
        'tourradar_status',
        'tourradar_reason',
        'tourradar_text',
        'duffel_id',
        'source',
        'device',
        'affiliate',
        'origin',
        'f_destination',
        'f_return',
        'f_duration',
        'destination_stops',
        'return_stops',
        'total_stops',
        'destination_carrier',
        'return_carrier',
        'checked_bags',
        'travelers_number',
        'reference',
        'method',
        'currency',
        'invoice',
        'paid',
        'fees',
        'markup',
        'refunded',
        'p_flight',
        'p_tour',
        'commission_value_tour',
        'discounted',
        'promo',
        'profit',
        'ratio',
        'user_id',
        'whole_trip',
        'channel',
        'payment_method',
        'medium',
        'gender',
        'age_group',
        'group_size',
        'country',
        'carrier',
        'payment_id',
        'created_at',
        'updated_at',
        'commission',
        'stripe_fee',
        'passengers'
    ];

    protected $appends = [
        'gross_profit',
        'gross_profit_ratio',
        'average_price_per_person_per_day',
        'flights',
        'last_4',
        'stripe_created',
        'last_charge',
    ];
    public function getLast4Attribute(): ?string
    {
        return $this->fetchStripeJson('data.payment_method_details.card.last4');
    }

    public function getLastChargeAttribute(): ?string
    {
        return $this->fetchStripeJson('data.payment_intent.latest_charge');
    }

    /**
     * Created timestamp from the PaymentIntent, wrapped in Carbon
     */
    public function getStripeCreatedAttribute(): ?Carbon
    {
        $ts = $this->fetchStripeJson('data.payment_intent.created');
        return $ts ? Carbon::createFromTimestamp($ts) : null;
    }

    /**
     * Shared helper to call your /stripe?q={payment_id} endpoint,
     * cache for 5 minutes, and pull a nested JSON key.
     */
    protected function fetchStripeJson(string $path)
    {
        return Cache::remember("order:{$this->id}:stripe", now()->addMinutes(5), function () {
            $resp = Http::get(route('stripe.query'), ['q' => $this->payment_id]);
            if (! $resp->successful()) {
                return null;
            }
            return $resp->json(); // full array
        }) 
        ? data_get(Cache::get("order:{$this->id}:stripe"), $path) 
        : null;
    }
    protected $casts = [
        'passengers' => 'array',
    ];
    
    public function getGrossProfitAttribute()
    {
        return  $this->paid - $this->paid_to_suppliers - $this->refunded;
    }

    public function getGrossProfitRatioAttribute()
    {
        return ($this->paid > 0) ? ($this->grossProfit / $this->paid) * 100 : 0;
    }

    public function getAveragePricePerPersonPerDayAttribute()
    {
        $startDate = Carbon::parse($this->start);
        $endDate = Carbon::parse($this->end);
        $days = $startDate->diffInDays($endDate) + 1;

        return ($days * $this->travelers_number > 0) ? $this->p_tour / ($days * $this->travelers_number) : 0;
    }

    public function flightTour()
    {
        return $this->hasOne(FlightTour::class, 'id_order', 'booking_id');
    }

    public function attempt()
    {
        return $this->hasOne(Attempt::class, 'booking_id', 'booking_id');
    }

    public function getFlightsAttribute()
    {
        $flights = FlightTour::where('id_order', $this->booking_id)->select('flight')->get();
        $flights = $flights->pluck('flight')->pluck('data')->pluck('slices');
        return $flights;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function travelers()
    {
        return $this->belongsToMany(Traveler::class, 'order_traveler', 'booking_id', 'traveler_id');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $list_days = (new ToursFilters)->getListDays();
        $list_whole_trip = (new ToursFilters)->getListWholeTrips();
        $list_age_group = (new ToursFilters)->getListAges();
        $list_hours = (new ToursFilters)->getListHours();


        $query->where(function ($query) use ($filters, $list_days){

            if($filters['booking']){
                in_array($filters['booking'],[1,2])?$query->orWhere('created_at',Carbon::parse($list_days[$filters['booking']])):
                $query->orWhereBetween('created_at',[Carbon::parse($list_days[$filters['booking']]['start']),Carbon::parse($list_days[$filters['booking']]['ends'])]);
            }

            if($filters['travel']){
                in_array($filters['travel'],[1,2])?$query->orWhere('start',Carbon::parse($list_days[$filters['travel']])):
                $query->orWhereBetween('start',[Carbon::parse($list_days[$filters['travel']]['start']),Carbon::parse($list_days[$r->travel]['ends'])]);
            }
        });

            //category (travel_style=type)
        !$filters['operator']?:$query->wherein('operator',explode(',', $filters['operator']));
        if ($filters['travel_style']) {
            $query->whereHas('tour.type', function ($query) use ($filters) {
                $query->wherein('tour_type_id', explode(',',$filters['travel_style']));
            });
        }

        if( $filters['destination_city']){
            $query->whereHas('tour.cities',function($query) use ($filters){
                $query->whereIn('t_city_id',explode(',',$filters['destination_city']));
            });
        }

        if( $filters['destination_country']){
            $query->whereHas('tour.countries',function($query) use ($filters){
                $query->whereIn('t_country_id',explode(',',$filters['destination_country']));
            });
        }

         //adventure
         !$filters['adventure']?:$query->wherein('tour_id',explode(',', $filters['adventure']));
         !$filters['status']?:$query->wherein('tourradar_status',explode(',', $filters['status']));
         if($filters['whole_trip']){
             $value=$list_whole_trip[$filters['whole_trip']];
             $filters['whole_trip']!=7?$query->WhereBetween('whole_trip',[$value[0],$value[1]]) :$query->where('whole_trip','>=',$value[0]);
         }

         if ($filters['duration']) {
             $val_d= $list_whole_trip[$filters['duration']];
             $query->whereHas('tour', function ($query) use ($val_d, $filters) {
              $filters['duration']==7?$query->where('tour_length_days','>=',31):$query->whereBetween('tour_length_days',[$val_d[0],$val_d[1]]);

             });
         }

         !$filters['carrier']?:$query->wherein('carrier',explode(',', $filters['carrier']));

           //traveler
        if($filters['age_group']){
            $val_age= $list_age_group[$filters['age_group']];
            $filters['age_group']==6?$query->where('age_group','>=',$val_age[0]):$query->whereBetween('age_group',[$val_age[0],$val_age[1]]);
        }

        if($filters['group_size']){
             $filters['group_size']<11?$query->wherein('group_size',explode(',', $filters['group_size'])):$query->where('group_size','>=',11);
        }

        !$filters['gender']?:$query->wherein('gender',explode(',', $filters['gender']));
        !$filters['country']?:$query->wherein('country',explode(',', $filters['country']));

          //booking
          !$filters['channel']?:$query->wherein('channel',explode(',', $filters['channel']));
          !$filters['payment_method']?:$query->wherein('payment_method',explode(',', $filters['payment_method']));

          !$filters['medium']?:$query->wherein('medium',explode(',', $filters['medium']));
          !$filters['day']?:$query->wherein(DB::raw('DAYOFWEEK(start)'),explode(',',$filters['day']));

          if ($filters['hour']) {
              $hours = $list_hours[$filters['hour']];
              $query->whereBetween(DB::raw('HOUR(created_at)'), $hours);
          }

        return $query;
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    public function type()
    {
        return $this->hasMany(Type::class, 'tour_type_id', 'tour_id');
    }

    public function operator()
    {
        return $this->hasOne(Operators::class, 'operator_id', 'operator');
    }

    public function natural_destination()
    {
        return $this->hasMany(TourNaturalDestination::class, 'tour_id', 'tour_id');
    }
}


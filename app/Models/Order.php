<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'booking_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'booking_id',
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
        'created_at',
        'updated_at'
    ];

    public function travelers()
    {
        return $this->belongsToMany(Traveler::class, 'order_traveler', 'booking_id', 'traveler_id');
    }
    
    protected $hidden = ['created_at', 'updated_at'];

    public function flightTour()
    {
        return $this->hasMany(FlightTour::class, 'id_order');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['fechaInicio'])) {
            $fechaInicio = Carbon::parse($filters['fechaInicio'])->startOfDay();
            $fechaFin = Carbon::parse($filters['fechaFin'])->endOfDay();
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        if (!empty($filters['destinations'])) {
            $query->whereIn('end_city', $filters['destinations']);
        }

        if (!empty($filters['operator'])) {
            $query->where('operator', $filters['operator']);
        }

        if (!empty($filters['adventure'])) {
            $query->where('tour_name', $filters['adventure']);
        }

        if (!empty($filters['status'])) {
            $query->where('booking_status', $filters['status']);
        }

        if (!empty($filters['duration_adventure'])) {
            $query->where('tour_length', $filters['duration_adventure']);
        }

        if (!empty($filters['duration_whole_trip'])) {
            $query->where('whole_trip', $filters['duration_whole_trip']);
        }

        if (!empty($filters['carrier'])) {
            $query->where('carrier', $filters['carrier']);
        }

        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}


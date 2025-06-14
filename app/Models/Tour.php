<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $table = 'tours';

    protected $primaryKey = 'tour_id';

 	protected $fillable = [
        'tour_id',
        'tour_name',
        'locale',
        'language',
        'is_active',
        'tour_length_days',
        'start_city',
        'end_city',
        'is_instant_confirmable',
        'price_total',
        'price_currency',
        'price_promotion',
        'reviews_count',
        'ratings_overall',
        'ratings_operator',
        'description',
        'min_age',
        'max_age',
        'max_group_size',
        'main_image',
        'main_thumbnail',
        'map_image',
        'map_thumbnail',
		'departures',
        'operator_id',
		'operator_name',
		'commission',
        'prices',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'tour_id' => 'integer',
        'tour_name' => 'string',
        'locale' => 'string',
        'language' => 'string',
        'is_active' => 'boolean',
        'tour_length_days' => 'integer',
        'start_city' => 'integer',
        'end_city' => 'integer',
        'is_instant_confirmable' => 'boolean',
        'price_total' => 'decimal:2',
        'price_currency' => 'string',
        'price_promotion' => 'decimal:2',
        'departures' => 'string', 
        'prices' => 'array',
    ];

    public function cities()
    {
        return $this->hasMany(TourCity::class, 'tour_id', 'tour_id')->with('city');
    }

    public function countries()
    {
        return $this->hasMany(TourCountry::class, 'tour_id', 'tour_id')->with('country');
    }

    public function tcountries()
    {
        return $this->hasMany(TourCountry::class, 'tour_id', 'tour_id');
    }

    public function natural_destination()
    {
        return $this->hasMany(TourNaturalDestination::class, 'tour_id', 'tour_id')->with('natural_destination');
    }

    public function type()
    {
        return $this->hasMany(TourType::class, 'tour_id', 'tour_id')->with('type');
    }


    public function scopeFilterByCountry($query, $countryIds)
    {
        return $query->whereHas('countries', function ($q) use ($countryIds) {
            $q->whereIn('t_country_id', $countryIds);
        });
    }

    public function scopeFilterByCity($query, $cityIds)
    {
        return $query->whereHas('cities', function ($q) use ($cityIds) {
            $q->whereIn('t_city_id', $cityIds);
        });
    }

    public function scopeFilterByNaturalDestination($query, $naturalIds)
    {
        return $query->whereHas('natural_destination', function ($q) use ($naturalIds) {
            $q->whereIn('t_natural_id', $naturalIds);
        });
    }

    public function scopeFilterByType($query, $typeIds)
    {
        return $query->whereHas('type', function ($q) use ($typeIds) {
            $q->whereIn('tour_type_id', $typeIds);
        });
    }

    public function city(){
        return $this->hasOne(City::class,'t_city_id','end_city');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'tour_id', 'tour_id');
    }
}

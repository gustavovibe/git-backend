<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;
    protected $table = 'tours';

    protected $fillable = [
        'tour_id',
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
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function cities()
    {
        return $this->hasMany(TourCity::class, 'tour_id', 'tour_id')->with('city');
    }

    public function countries()
    {
        return $this->hasMany(TourCountry::class, 'tour_id', 'tour_id')->with('country');
    }

    public function natural_destination()
    {
        return $this->hasMany(TourNaturalDestination::class, 'tour_id', 'tour_id')->with('natural_destination');
    }

    public function type()
    {
        return $this->hasMany(TourType::class, 'tour_id', 'tour_id')->with('type');
    }
}

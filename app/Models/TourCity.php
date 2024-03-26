<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourCity extends Model
{
    use HasFactory;
    protected $table = 'tour_cities';

    protected $fillable = [
        'tour_id',
        't_city_id',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 't_city_id', 't_city_id');
    }
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}

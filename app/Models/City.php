<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        't_city_id',
        'city_name',
        't_country_id',
        'destination_id',
    ];

    public function tours()
    {
        return $this->hasMany(TourCity::class, 't_city_id', 't_city_id');
    }

    public function destination()
    {
        return $this->hasOne(Destination::class, 'id', 'destination_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 't_country_id', 'country_code');
    }

}

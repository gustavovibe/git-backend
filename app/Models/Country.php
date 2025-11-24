<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        't_country_id',
        'name',
        'country_code',
        'destination_id',
    ];

    public function tours()
    {
        return $this->hasMany(TourCountry::class, 't_country_id', 't_country_id');
    }

    public function destination()
    {
        return $this->hasOne(Destination::class, 'id', 'destination_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'country_code', 't_country_id');
    }
}

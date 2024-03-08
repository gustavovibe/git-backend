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
        'kiwi_id',
        't_country_id'
    ];
}

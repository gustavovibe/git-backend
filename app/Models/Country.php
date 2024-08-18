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
        'created_at', 'updated_at'
    ];

    public function tours()
    {
        return $this->hasMany(TourCountry::class, 't_country_id', 't_country_id');
    }
}

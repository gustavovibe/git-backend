<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourCountry extends Model
{
    use HasFactory;
    protected $table = 'tour_countries';

    protected $fillable = [
        'tour_id',
        't_country_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function country()
    {
        return $this->belongsTo(Country::class, 't_country_id', 't_country_id');
    }
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}

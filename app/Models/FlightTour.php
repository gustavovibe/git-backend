<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightTour extends Model
{
    use HasFactory;

    protected $table = 'flights_tours';

    protected $fillable = [
        'flight',
        'tour',
        'id_order'
    ];

    protected $casts = [
        'flight' => 'array',
        'tour' => 'array',
    ];

}

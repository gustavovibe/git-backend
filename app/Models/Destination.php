<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'overview',
        'quick_facts',
        'things_to_do',
        'travel_tips',
        'best_time_to_visit',
        'slug',
        'excerpt',
        'meta_description',
    ];
}

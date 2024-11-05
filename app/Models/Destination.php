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
        'qf_population',
        'qf_capital',
        'qf_area',
        'qf_currency',
        'qf_official_language',
        'qf_country_code',
        'qf_plug_type',
        'qf_time_zone',
        'qf_high_season',
        'things_to_do',
        'travel_tips',
        'best_time_to_visit',
        'slug',
        'excerpt',
        'meta_description',
    ];
}

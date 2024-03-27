<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourNaturalDestination extends Model
{
    use HasFactory;
    protected $table = 'tour_natural_destinations';

    protected $fillable = [
        'tour_id',
        't_natural_id',
    ];


    public function natural_destination()
    {
        return $this->belongsTo(NaturalDestination::class, 't_natural_id', 'destination_id');
    }
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}

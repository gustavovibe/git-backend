<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturalDestination extends Model
{
    use HasFactory;

    protected $table = 'natural_destinations';

    protected $fillable = [
        't_natural_id',
        'destination_name',
        'type',
        'destination_id',
    ];

    public function tours()
    {
        return $this->hasMany(TourNaturalDestination::class, 't_natural_id', 't_natural_id');
    }

    public function destination()
    {
        return $this->hasOne(Destination::class, 'id', 'destination_id');
    }
}

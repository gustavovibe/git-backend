<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturalDestination extends Model
{
    use HasFactory;
    protected $table = 'natural_destinations'; 

    protected $fillable = [
        'destination_id',
        'destination_name',
        'type',
    ];
}

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
    ];
    protected $hidden = ['created_at', 'updated_at'];
}

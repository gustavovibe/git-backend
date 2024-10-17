<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiries extends Model
{
    use HasFactory;

    protected $fillable=[
        'departure_date',
        'name',
        'last_name',
        'email',
        'phone',
        'travelers',
        'message',
    ];
}

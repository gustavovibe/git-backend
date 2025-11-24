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
        'topic',
        'booking_id',
        'adventure_link',
        'tour_details',
    ];

    protected $casts = [
        'tour_details' => 'array',
    ];
}

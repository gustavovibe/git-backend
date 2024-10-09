<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSummary extends Model
{
    use HasFactory;
    protected $fillable=[
        'email',
        'tour_id'
    ];
}

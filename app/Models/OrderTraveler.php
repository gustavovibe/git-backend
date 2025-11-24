<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTraveler extends Model
{
    use HasFactory;
    protected $table="order_traveler";
    protected $fillable = ['booking_id', 'traveler_id'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'booking_id', 'booking_id');
    }

    public function traveler()
    {
        return $this->belongsTo(Traveler::class, 'traveler_id', 'traveler_id');
    }
}

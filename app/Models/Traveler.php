<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traveler extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'gender',
        'name',
        'last',
        'birth',
        'passport',
        'place',
        'issue',
        'expire',
        'mail',
        'phone',
        'address',
        'country',
        'lead',
        'created_at',
        'updated_at'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_traveler', 'traveler_id', 'booking_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}

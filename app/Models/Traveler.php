<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traveler extends Model
{
    use HasFactory;

    protected $table = 'travelers';
    protected $primaryKey = 'traveler_id';
    public $incrementing = true;
    protected $keyType = 'int';
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
        'user_id',
        'status',
        'phone_country'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_traveler', 'traveler_id', 'booking_id');
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user_()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}

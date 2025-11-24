<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'profile_id', 
        'phone', 
        'country', 
        'role', 
        'active', 
        'suscribed', 
        'hear',
        'job_id',
        'last_login',
        'internal_notes',
        'phone_country', 
        'inspired_travel_preference', 
        'new_departures_preference',
    ];

    protected $hidden = [
        'password','created_at', 'updated_at'
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function permission(){
        return $this->hasMany(Permission_User::class,'user_id','id');
    }


    public function job(){
        return $this->hasOne(Job::class,'id','job_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function traveler()
    {
        return $this->hasOne(Traveler::class, 'user_id', 'id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'id');
    }

}


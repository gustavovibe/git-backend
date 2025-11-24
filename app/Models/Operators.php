<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operators extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'operator_id',
        'active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function tours(){
        return $this->hasMany(Tour::class,'operator_id','operator_id');
    }

    public function orders(){
        return $this->hasMany(Order::class,'operator','operator_id');
    }
}

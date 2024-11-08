<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    use HasFactory;

    public $fillable=[
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    public function user(){
        return $this->hasOne(User::class,'email','email');
    }
}

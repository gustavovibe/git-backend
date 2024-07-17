<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'profile_id', 'phone','phone_code', 'active',
    ];

    protected $hidden = [
        'password','created_at', 'updated_at'
    ];



    public function profile()
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    public function permission(){
        return $this->hasMany(SystemPermission_User::class,'user_id','id');
    }

}

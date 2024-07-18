<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'phone','phone_code', 'active','job_id'
    ];

    protected $hidden = [
        'password','created_at', 'updated_at'
    ];


    public function permission(){
        return $this->hasMany(SystemPermission_User::class,'user_id','id');
    }

    public function job(){
        return $this->hasOne(Job::class,'id','job_id');
    }

}

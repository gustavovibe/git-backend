<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPermission_User extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'permission_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'pivot'
    ];

    public function users()
    {
        return $this->belongsToMany(SystemUser::class, 'system_permission_user', 'permission_id', 'user_id');
    }

    public function details(){
        return $this->hasOne(Permission::class,'id','permission_id');
    }
}

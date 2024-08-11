<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\SystemPermission_User;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    use HandlesAuthorization;


    public function viewAny(User $user)
    {
        $p=Permission::where('description','inventory')->select('id')->first();
        $usp=SystemPermission_User::where('user_id',$user->id)->where('permission_id',$p->id)->first();
        return  !empty($usp);
    }


    public function view(User $user, Permission $permission)
    {
        return !empty($user->permission['permissions']['inventory']);
    }


    public function create(User $user)
    {
        return !empty($user->permissions['permissions']['inventory']);
    }


    public function update(User $user, Permission $permission)
    {
        return !empty($user->permissions['permissions']['inventory']);
    }


    public function delete(User $user, Permission $permission)
    {
        //
    }


    public function restore(User $user, Permission $permission)
    {
        //
    }


    public function forceDelete(User $user, Permission $permission)
    {
        //
    }
}

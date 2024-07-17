<?php

namespace App\Filters;

use App\Models\ContactEmail;
use App\Models\SystemUser;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UsersFilters
{
    protected $notifications;
    protected $permissions;
    public function __construct(){

        $this->permissions=[
            'inventory'=>false,
            'orders_p'=>false,
            'travelers'=>false,
            'reports'=>false,
            'users'=>false,
            'emails'=>false,
            'actions'=>false,
        ];

        $this->notifications=[
            'orders_n'=>false,
            'cart'=>false,
            'bounced'=>false,
            'report'=>false
        ];
    }

    public function UsersF(Request $r){
        $users = (new SystemUser)->newQuery();
        !$r->name?:$users->where('name', 'like', '%' . $r->name . '%');
        !$r->admin?:$users->select('id', 'name', 'email', 'phone', 'phone_code', 'profile_id');
        !$r->limit?:$users->limit($r->limit);

        $users = $users->get();

        $users = $users->map(function($u) {
            $permissions = $this->permissions;
            $notifications =$this->notifications;
            $u->phone = (int) $u->phone;
            if ($u->permission && is_iterable($u->permission)) {
                $u->permission->map(function($uu) use (&$permissions, &$notifications) {
                    $description = $uu->details->description ?? null;

                    if ($description) {
                        if (array_key_exists($description, $this->permissions)) {
                            $permissions[$description] = true;
                        }
                        if (array_key_exists($description, $this->notifications)) {
                            $notifications[$description] = true;
                        }
                    }
                    unset($uu->details);
                    return $uu;
                })->values()->all();
            }
            $u->permissions = $permissions;
            $u->notifications = $notifications;

            unset($u->permission);
            return $u;
        })->values()->all();

        return $users;
    }
}

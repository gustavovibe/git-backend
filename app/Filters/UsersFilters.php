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
        $admin=$r->admin;
        $filter=$r->filter;
        $users = (new SystemUser)->newQuery();
        !$r->name?:$users->where('name', 'like', '%' . $r->name . '%');
        !$r->id?:$users->where('id', $r->id);
        !$r->admin?:$users->select('id', 'name', 'email', 'phone', 'phone_code', 'job_id');
        !$r->limit?:$users->limit($r->limit);
        !$r->filter?:$users->where(function($query)use($filter){
            $query->where('name', 'like', '%' . $filter . '%')
            ->orWhere('email', 'like', '%' . $filter . '%');
        });
        $users = $users->get();

        $users = $users->map(function($u) use($admin) {
            $u->phone = (int) $u->phone;
            $u->job_title=$u->job->name;
            $permissions =$admin?$this->permissions:[];
            $notifications =$this->notifications;
            if ($u->permission && is_iterable($u->permission)) {
                $u->permission->map(function($uu) use (&$permissions, &$notifications,$admin) {
                        $description = $uu->details->description ?? null;

                        if ($description) {
                            if (array_key_exists($description, $this->permissions)) {
                               !$admin?:$permissions[$description] = true;
                               $admin?:array_push($permissions,$description);
                            }
                        if($admin){
                            if (array_key_exists($description, $this->notifications)) {
                                $notifications[$description] = true;
                            }
                        }
                        }
                        unset($uu->details);
                        return $uu;
                    })->values()->all();
                }
                $u->permissions = $permissions;
                $admin?:$u->permissions=implode(',',$u->permissions);
                !$admin?:$u->notifications = $notifications;

            unset($u->job);
            unset($u->permission);
            return $u;
        })->values()->all();

        return $users;
    }
}

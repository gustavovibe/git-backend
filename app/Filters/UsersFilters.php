<?php

namespace App\Filters;

use App\Models\ContactEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
class UsersFilters
{
    protected $notifications;
    protected $permissions;
    protected $permission_text;

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

        $this->permission_text=[
            'inventory'=>'Inventory',
            'orders_p'=>'Orders',
            'travelers'=>'Travelers',
            'reports'=>'Reports',
            'users'=>'Settings: Users',
            'emails'=>'Settings: Emails',
            'actions'=>'Settings: Action logs',
        ];
    }

    public function UsersF(Request $r){

        $admin=$r->admin;
        $filter=$r->filter;
        $users = (new User)->newQuery();

        !$r->name?:$users->where('name', 'like', '%' . $r->name . '%');
        !$r->id?:$users->where('id', $r->id);
        !$r->admin?:$users->select('id', 'name', 'email', 'phone', 'job_id','password','last_login','profile_id','country')->where('profile_id',1);
        !$r->limit?:$users->limit($r->limit);
        !$r->filter?:$users->where(function($query)use($filter){
            $query->where('name', 'like', '%' . $filter . '%')
            ->orWhere('email', 'like', '%' . $filter . '%');
        });
        $users->where('active',1);
        $users = $users->get();
        $users = $users->map(function($u) use($admin) {
            $u->phone = (int) $u->phone;
            $u->job_title=$u->job->name;
            !$admin?:$u->code=$u->password;
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
                if(!$admin){
                    $val=[];
                    foreach( $u->permissions as $p){
                        $val[]=$this->permission_text[$p];
                    }
                    $u->permissions=$val;
                    $admin?:$u->permissions=implode(',',$u->permissions);
                }
                !$admin?:$u->notifications = $notifications;

            unset($u->job);
            unset($u->permission);
            return $u;
        })->values();

        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $users = new LengthAwarePaginator(
            $users->forPage($currentPage, $perPage),
            $users->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );
        return $users;
    }
}

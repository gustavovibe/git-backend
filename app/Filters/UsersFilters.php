<?php

namespace App\Filters;

use App\Models\ActionLog;
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
    protected $list_days;
    protected $type_list;
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

        $this->list_days=[
            1=>Carbon::today(),
            2=>Carbon::yesterdaY(),
            3=>['start'=>Carbon::now()->subDays(7)->startOfDay(),'ends'=>Carbon::now()->endOfDay()],//ultimos 7 dias
            4=>['start'=>Carbon::now()->subDays(30)->startOfDay(),'ends'=>Carbon::now()->endOfDay()],//ultimos 30 dias
            5=>['start'=>Carbon::now()->startOfWeek(),'ends'=>Carbon::now()->endOfWeek()],//semana actual
            6=>['start'=>Carbon::now()->subWeek()->startOfWeek(),'ends'=>Carbon::now()->subWeek()->endOfWeek()],//semana pasada
            7=>['start'=>Carbon::now()->startOfMonth(),'ends'=>Carbon::now()->endOfMonth()],//mes actual
            8=>['start'=>Carbon::now()->subMonth()->startOfMonth(),'ends'=>Carbon::now()->subMonth()->endOfMonth()],//mes pasado
            9=>['start'=>Carbon::now()->startOfYear(),'ends'=>Carbon::now()->endOfYear()],//este año
            10=>['start'=>Carbon::now()->subYear()->startOfYear(),'ends'=>Carbon::now()->subYear()->endOfYear()],//año pasado
        ];

        $this->type_list=[
            1=>'Order',
            2=>'Traveler',
            3=>'Destination',
            4=>'Travel style',
            5=>'Users',
        ];
    }

    public function UsersF(Request $r){

        $admin=$r->admin;
        $filter=$r->filter;
        $users = (new User)->newQuery();

        !$r->name?:$users->where('name', 'like', '%' . $r->name . '%');
        !$r->id?:$users->where('id', $r->id);
        !$r->role?:$users->where('role', $r->role);
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
            $u->job_title=$u->job?$u->job->name:'N/A';
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

    public function ActionLogs(Request $r){
        $action= ActionLog::query();
        $name=$r->name;
        $users_id=$r->users_id?explode(',',$r->users_id):[];
        if($name){
            $action->wherehas('user',function($a) use($name) {
                $a->where('name','like',"%{$name}%");
            });
        }
        if($r->dates){
            in_array($r->dates,[1,2])?$action->where('created_at',Carbon::parse($this->list_days[$r->dates])):
            $action->whereBetween('created_at',[Carbon::parse($this->list_days[$r->dates]['start']),Carbon::parse($this->list_days[$r->dates]['ends'])]);
        }
        !count($users_id)>0?:$action->wherein('user_id',$users_id);
        !$r->type?:$action->where('type',$r->type);
        !$r->user_id?:$action->where('user_id',$r->user_id);
        $action = $action->get()->map(function ($actions) {
            $actions->action_date =Carbon::parse($actions->created_at)->format('d M Y, g:i a');
            $actions->email=$actions->user->email;
            $actions->type_name=$this->type_list[$actions->type];
            unset($actions->user);
            return $actions;
        })->values();

        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $action = new LengthAwarePaginator(
            $action->forPage($currentPage, $perPage),
            $action->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );

        return $action;
    }
}

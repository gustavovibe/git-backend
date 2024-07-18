<?php

namespace App\Http\Controllers;

use App\Filters\UsersFilters;
use App\Models\Permission;
use App\Models\SystemPermission_User;
use App\Models\SystemUser;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class SystemUserController extends Controller
{
    protected $email_validations;
    public function __construct()
    {
        $this->email_validations=[
            '0'=>'Correct',
            '1'=>'Email cannot be empty',
            '2'=>'This email is actually register',
            '3'=>'This email required a correct format'
        ];
    }

    public function createUser(Request $r){
        /* return response()->json(['status'=>200,'response'=>$r->all()]); */
        DB::beginTransaction();
        try{
            $u=$r->id?SystemUser::find($r->id):new SystemUser;
            $u->fill([
                'name'=>$r->name,
                'email'=>$r->email,
                'phone'=>$r->phone,
                'phone_code'=>$r->phone_code,
                'job_id'=>$r->job_id,
            ])->save();

           $existingPermissions = SystemPermission_User::where('user_id', $u->id)->pluck('permission_id')->toArray();

           foreach ($r->permissions as $key => $value) {
            $p = Permission::where('description', $key)->first();
            if ($p) {
                if ($value) {
                    if (!in_array($p->id, $existingPermissions)) {
                        $usp = new SystemPermission_User;
                        $usp->fill([
                            'user_id' => $u->id,
                            'permission_id' => $p->id,
                        ])->save();
                    }
                } else {
                    SystemPermission_User::where('user_id', $u->id)->where('permission_id', $p->id)->delete();
                    }
                }
            }

            DB::commit();
            return response()->json(['status'=>200,'response'=>$r->all()]);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }


    public function getUsers(Request $r){
        try {
            $user = SystemUser::with('permission')->find($r->user_id);
           /*  return $this->authorize('viewAny',$user); */
           $u = (new UsersFilters)->UsersF($r);
           return response()->json(['status' => 200,'count'=>count($u),'response' => $u]);
         /*   if (Gate::forUser($user)->allows('viewAny', SystemPermission_User::class)) {
            } else {
                return response()->json(['status' => 500, 'response' => 'User has not Access']);
            } */
        }catch (Error $e) {
            Log::error('Exception: ' . $e->getMessage());
            return response()->json(['status' => 500, 'response' => $e->getMessage()]);
        }
    }

    public function deleteUsers(Request $r){
        DB::beginTransaction();
        try{
            $user = SystemUser::find($r->user_id);
            $perm= SystemPermission_User::where('user_id',$r->user_id)->get();
            foreach ($perm as $permission) {
                $permission->delete();
            }
            $user->delete();
            DB::commit();
            return response()->json(['status' => 200, 'response' => ['user'=>$user,'perm'=>$perm]]);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status' => 500, 'response' => $e]);
        }
    }

    public function validateEmail(Request $request) {
        // Definir las reglas de validación
        $rules = [
            'email' => 'required|email|unique:system_users,email'
        ];

        // Mensajes de error personalizados
        $messages = [
            'required' => 'The email field is required.',
            'email' => 'The email must be a valid email address.',
            'unique' => 'The email is already registered.'
        ];

        // Realizar la validación
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $error=$validator->errors()->toArray();
            return response()->json(['status' => false, 'response' =>['message'=>$error,'value'=>false]]);
        }

        return response()->json(['status' => true, 'response' => 'The email is valid and not registered.']);
    }
}

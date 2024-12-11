<?php

namespace App\Http\Controllers;

use App\Filters\UsersFilters;
use App\Models\ActionLog;
use App\Models\Permission;
use App\Models\Permission_User;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
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

    /**
     * createUser.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function createUser(Request $r){
        /* return response()->json(['status'=>200,'response'=>$r->all()]); */
        DB::beginTransaction();
        try{
            $u=$r->id?User::find($r->id):new User;
            $random=Str::random(10);
            $u->fill([
                'name'=>$r->name,
                'email'=>$r->email,
                'phone'=>$r->phone,
                'country'=>$r->country,
                'job_id'=>$r->job_id,
                'profile_id'=>$r->id?$u->profile_id:1,
                'active'=>$r->id?$u->active:1,
                'role'=>$r->id?$u->role:1,
                'password'=>$r->id?$u->password:Hash::make($random)
            ])->save();


           $existingPermissions = Permission_User::where('user_id', $u->id)->pluck('permission_id')->toArray();

           foreach ($r->permissions as $key => $value) {
            $p = Permission::where('description', $key)->first();
            if ($p) {
                if ($value) {
                    if (!in_array($p->id, $existingPermissions)) {
                        $usp = new Permission_User;
                        $usp->fill([
                            'user_id' => $u->id,
                            'permission_id' => $p->id,
                        ])->save();
                    }
                } else {
                    Permission_User::where('user_id', $u->id)->where('permission_id', $p->id)->delete();
                    }
                }
            }
            ActionLog::create([
                'user_id' => $u->user_log,
                'type' =>$r->id?'Update':'Created',
                'action' =>$r->id? 'User update successfully':'User created successfully',
                'item' => 'User',
            ]);
            DB::commit();
            return response()->json(['status'=>200,'response'=>$r->all()]);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }


    /**
     * Get Users by filters
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function getUsers(Request $r){
        try {
           $u = (new UsersFilters)->UsersF($r);
           return response()->json(['status' => 200,'count'=>count($u),'response' => $u]);
        }catch (Error $e) {
            Log::error('Exception: ' . $e->getMessage());
            return response()->json(['status' => 500, 'response' => $e->getMessage()]);
        }
    }

    /**
     * Delete Users
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function deleteUsers(Request $r){
        DB::beginTransaction();
        try{
            $user = SystemUser::find($r->user_id);
            $user->active=0;
            $user->save();

            ActionLog::create([
                'user_id' => $r->user_log,
                'type' => 'Delete',
                'action' =>'User deleted successfully',
                'item' => 'User',
            ]);
            DB::commit();
            return response()->json(['status' => 200, 'response' => ['user'=>$user,'perm'=>$perm]]);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status' => 500, 'response' => $e]);
        }
    }

    /**
     * Validate email.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function validateEmail(Request $request) {
        // Definir las reglas de validación
        $rules = [
            'email' => 'required|email|unique:users,email'
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Error;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserByEmail(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json([
                'status' => false,
                'message' => 'Email query parameter is required.'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Customize the attributes you want to return
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country' => $user->country,
            'role' => $user->role,
            'active' => $user->active,
            'suscribed' => $user->suscribed,
            'hear' => $user->hear,
        ];

        return response()->json([
            'status' => true,
            'user' => $userData
        ], 200);
    }

    public function createUser(Request $r){
        try{
           /*  $u=$r->id?User::find($r->id):new User;
            $u->fill([
                'name'=>$r->name,
                'email'=>$r->email,
                'profile_id'=>$r->profile_id,
                'phone'=>$r->phoneNumber
            ]); */
            return response()->json(['status'=>200,'response'=>$r->all()]);
        }catch(Error $e){
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }


    public function getUsers(){
        try{
            return response()->json(['status'=>200,'response'=>'']);
        }catch(Error $e){
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }
}

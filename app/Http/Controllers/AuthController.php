<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailRegistro;
use App\Models\Traveler;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Google\Client;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_id' => $request->profile_id,
            'phone' => $request->phone,
            'country' => $request->country,
            'role' => $request->role,
            'active' => $request->active,
            'suscribed' => $request->suscribed,
            'hear' => $request->hear,
        ]);

        ActionLog::create([
            'user_id' => $user->id,
            'type' => 'Created',
            'action' =>'User created successfully',
            'item' => 'User',
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Registro exitoso'
        ], 200);
    }

    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'unauthorized.'
            ], 401);
        }

        $user = User::where('email', $request['email'])->with('profile', 'permissions')->firstOrFail();
        $user->last_login = Carbon::now();
        $user->save();

        $traveler= Traveler::where('user_id',$user->id)->first();
        $user->traveler_id=$traveler->traveler_id;
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        ActionLog::create([
            'user_id' => $user->id,
            'type' => 'Login',
            'action' =>'User Login successfully',
            'item' => 'User',
        ]);

        return ApiResponse::success([
            'access_token' => $token,
            'user' => $user,
        ], 'Successful login');

    }

    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->tokens()->delete();

            ActionLog::create([
                'user_id' => $user->id,
                'type' => 'Logout',
                'action' =>'User Logout successfully',
                'item' => 'User',
            ]);
            return response()->json([
                'status' => true,
                'message' => 'logout successful'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No autorizado. Debes iniciar sesión para acceder a esta información.'
            ], 401);
        }
    }


    public function test(Request $request)
    {
        return "demo";
    }

    public function googleRegister(Request $r){
        try{
            $client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
                $payload = $client->verifyIdToken($r->token);
                   if ($payload) {
                    $randomPassword = Str::random(12);
                    $user=User::where('email',$payload['email'])->first();
                    $action='';
                        if(!$user){
                            $user = User::fill([
                                'email'=>$payload['email'],
                                'name' => $payload['name'],
                                'password' =>Hash::make($randomPassword),
                                'profile_id' => 1,
                                'role' => 1,
                                'active' => 1,
                                'suscribed' => 1,
                                'last_login'=>Carbon::now(),
                            ])->save();
                            $action='Register';
                        }else{
                            $user->last_login = Carbon::now();
                            $user->save();
                            $action='Login';
                        }

                        ActionLog::create([
                            'user_id' => $user->id,
                            'type' => $action,
                            'action' =>'User '.$action.' successfully',
                            'item' => 'User',
                        ]);
                        return response()->json(['success' => true, 'data' => $user]);
                   }
        }catch(Exception $e){
            return response()->json(['success'=>true,'data'=>$e->getMessage()]);
        }

    }
}

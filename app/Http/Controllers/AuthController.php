<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecoverMail;
use App\Models\PasswordResets;
use App\Models\Traveler;
use Carbon\Carbon;
use Exception;
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

    // public function googleRegister(Request $r){
    //     try{
    //         $client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
    //             $payload = $client->verifyIdToken($r->token);
    //                if ($payload) {
    //                 $randomPassword = Str::random(12);
    //                 $user=User::where('email',$payload['email'])->first();
    //                 $action='';
    //                     if(!$user){
    //                         $user = User::fill([
    //                             'email'=>$payload['email'],
    //                             'name' => $payload['name'],
    //                             'password' =>Hash::make($randomPassword),
    //                             'profile_id' => 1,
    //                             'role' => 1,
    //                             'active' => 1,
    //                             'suscribed' => 1,
    //                             'last_login'=>Carbon::now(),
    //                         ])->save();
    //                         $action='Register';
    //                     }else{
    //                         $user->last_login = Carbon::now();
    //                         $user->save();
    //                         $action='Login';
    //                     }

    //                     ActionLog::create([
    //                         'user_id' => $user->id,
    //                         'type' => $action,
    //                         'action' =>'User '.$action.' successfully',
    //                         'item' => 'User',
    //                     ]);
    //                     return response()->json(['success' => true, 'data' => $user]);
    //                }
    //     }catch(Exception $e){
    //         return response()->json(['success'=>true,'data'=>$e->getMessage()]);
    //     }

    // }


    public function googleRegister(Request $r)
    {
        try {
            \Log::info('Google Register: Received token: ' . $r->token);

            // Initialize Google client
            $client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            \Log::info('Google Client initialized successfully.');

            // Set the ID token manually
            $client->setAccessToken(['id_token' => $r->token]);

            // Verify ID token
            $payload = $client->verifyIdToken($r->token);

            \Log::info('Token verified. Payload:', $payload);

            if ($payload) {
                // Proceed with user retrieval/creation logic
                $user = User::where('email', $payload['email'])->first();

                if (!$user) {
                    \Log::info('Creating new user for email: ' . $payload['email']);
                    $user = new User([
                        'email' => $payload['email'],
                        'name' => $payload['name'],
                        'password' => Hash::make(uniqid()),
                        'profile_id' => 1,
                        'role' => 1,
                        'active' => 1,
                        'suscribed' => 1,
                        'last_login' => Carbon::now(),
                    ]);
                    $user->save();
                    $action = 'Register';
                } else {
                    \Log::info('Updating last login for existing user: ' . $user->id);
                    $user->last_login = Carbon::now();
                    $user->save();
                    $action = 'Login';
                }

                \Log::info('Logging action: ' . $action);
                ActionLog::create([
                    'user_id' => $user->id,
                    'type' => $action,
                    'action' => 'User ' . $action . ' successfully',
                    'item' => 'User',
                ]);

                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'imageUrl' => $payload['picture'] ?? null,
                    'profile_id' => $user->profile_id,
                ]);
            } else {
                \Log::warning('Invalid Google token');
                return response()->json(['error' => 'Invalid Google token'], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Google Register Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

    public function recoverPass(Request $r){
        try{
            $user= User::where('email',$r->email)->first();
            if(!$user){
                return response()->json(['success'=>false,'data'=>'User not found']);
            }
            $token= Str::random(60);
            $expires_at= Carbon::now()->addMinutes(15);
            PasswordResets::updateOrInsert(
                ['email'=>$user->email],
                [
                    'email'=>$user->email ,
                    'token'=>$token,
                    'created_at'=>Carbon::now(),
                    'expires_at'=>$expires_at,
                ]
                );

              $user->url="https://hopeful-nobel.74-208-189-166.plesk.page/reset-password?token={$token}";
            Mail::to($user->email)->send(new RecoverMail($user));
            return response()->json(['success'=>true,'data'=>'Please check your inbox!!!']);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    public function checkToken(Request $r){
        try{
            $reset_pass= PasswordResets::where('token',$r->token)->first();

            if(!$reset_pass || $reset_pass->expires_at < Carbon::now()){
                return response()->json(['success'=>false,'data'=>'The reset link has not available.']);
            }

            return response()->json(['success'=>true,'data'=>$reset_pass->user->id]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }
}

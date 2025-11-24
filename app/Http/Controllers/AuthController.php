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
use App\Models\ActionLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    /**
     * Register a new user.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function register(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => [
									'required',
									'string',
									'min:8',
									'regex:/[a-z]/',
									'regex:/[A-Z]/',
									'regex:/[0-9]/',
									'regex:/[^\p{L}\p{N}]/u',
                ],
            ]);

            if ($validator->fails()) {

							$message = json_decode(json_encode($validator->errors()), true);
							$error_msg = [];
							foreach($message as $key => $val){
								$error_msg[] = $val[0];
							}
							$error_msg[] = 'Password must have 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character.';
							return ApiResponse::error(implode("\n", $error_msg), 422);

            }

            if(User::where('email',$request->email)->first()){
                return response()->json(['status'=>false,'message'=>'Email already register']);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' =>2,
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            ActionLog::create([
                'user_id' => $user->id,
                'type' => 'Created',
                'action' =>'User created successfully',
                'item' => 'User',
            ]);
            return response()->json([
                'status' => true,
								'success' => true,
                'message' => 'Login successful',
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_id' => $user->profile_id,
                ],
            ]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage()]);
        }

    }

    /**
     * Login a user.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'unauthorized.'
            ], 401);
        }

        // Verificar que el usuario esté activo
        $user = Auth::user();
        if (!$user->active) {
            Auth::logout(); // Cierra sesión si estaba autenticado
            return response()->json(['error' => 'Account is not active.'], 401);
        }

        $user = User::where('email', $request['email'])->with('profile', 'permissions')->firstOrFail();
        $user->last_login = Carbon::now();
        $user->save();

        $traveler= Traveler::where('user_id',$user->id)->first();
        $user->traveler_id= $user->traveler_id?$traveler->traveler_id:0;
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        ActionLog::create([
            'user_id' => $user->id,
            'type' => 'Login',
            'action' =>'User Login successfully',
            'item' => 'User',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_id' => $user->profile_id,
            ],
        ], 200);

    }

    /**
     * Logout a user.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function logout(Request $r)
    {
        if (Auth::check()) {
            $user = Auth::user();
           /*  Log::info("Logging out user ID: " . $r->user_id); */

            $user->tokens()->delete(); // Delete tokens for authenticated user
            ActionLog::create([
                'user_id' => $r->user_id,
                'type' => 'Logout',
                'action' => 'User Logout successfully',
                'item' => 'User',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Logout successful'
            ], 200);
        } else {
            // Log::warning("Unauthorized logout attempt");
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

    /**
     * Google register.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function googleRegister(Request $r)
    {
        try {
            \Log::info('Google Register: Received token: ' . $r->token);

            $client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            \Log::info('Google Client initialized successfully.');

            $payload = $client->verifyIdToken($r->token);
            \Log::info('Token verified. Payload:', $payload);

            if ($payload) {
                $user = User::where('email', $payload['email'])->first();
                if (!$user) {
                    \Log::info('Creating new user for email: ' . $payload['email']);
                    $user = new User([
                        'email' => $payload['email'],
                        'name' => $payload['name'],
                        'password' => Hash::make(Str::random(16)), // Usar una contraseña segura
                        'profile_id' => 2,
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
                    $user->tokens()->delete();
                    $action = 'Login';
                }

                $token = $user->createToken('auth_token')->plainTextToken;

                \Log::info('Logging action: ' . $action);

                ActionLog::create([
                    'user_id' => $user->id,
                    'type' => $action,
                    'action' => 'User ' . $action . ' successfully',
                    'item' => 'User',
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'access_token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_id' => $user->profile_id,
                    ],
                ]);
            } else {
                \Log::warning('Invalid Google token');
                return response()->json(['error' => 'Invalid Google token'], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Google Register Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during registration. Please try again later.'], 500);
        }
    }


    /**
     * Recover password.
     *
     * Endpoint to recover password.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
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

              $user->url = rtrim(config('frontend.url'), '/') . "/reset-password?token={$token}";

            Mail::to($user->email)->send(new RecoverMail($user));
            return response()->json(['success'=>true,'data'=>'Please check your inbox']);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    /**
     * Check token.
     *
     * Endpoint to check token.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailRegistro;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


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

        // $correo = new MailRegistro($request->email, $request->password, $request->full_name);

        // Mail::to($request->email)->send($correo);

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
        $user->last_login=Carbon::now();
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Inicio de sesion correcto',
            'access_token' => $token,
            'user' => $user,
        ], 200);
    }

    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->tokens()->delete();
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
}

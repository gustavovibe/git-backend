<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailRegistro;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',


        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'status' => true,
            'message' => 'Inicio de sesion correcto',
            'access_token' => $token,
            'user' => $user->id,
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

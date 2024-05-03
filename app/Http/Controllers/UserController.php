<?php

namespace App\Http\Controllers;

use App\Mail\MailCursos;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\MiCorreo;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {
        $perPage = 10;

        $users = User::paginate($perPage);

        return $users;
    }




    public function enviarCorreoCursos(Request $request)
    {
        $email = $request->email;

        $correo = new MailCursos();

        Mail::to($email)->send($correo);

        return ' Correo enviado';
    }

    public function contactosAll()
    {
        $perPage = 10;

        $contact = Contact::paginate($perPage);

        return $contact;
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'id_profile' => 'required|integer',


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
            'id_profile' => $request->id_profile,

        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registro exitoso'
        ], 200);
    }


    public function show($id)
    {
        $perPage = 10;

        $user = User::with('profile', 'courses.category', 'courses.video')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user,
        ], 200);
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'id_profile' => 'required|integer',
        ]);

        $user->update($validatedData);

        return response()->json(['message' => 'Usuario actualizado con éxito', 'user' => $user]);
    }


    public function destroy($id)
    {
        $user = User::find($id);


        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado con éxito']);
    }
}

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

        // Utiliza el método 'with' para cargar la relación 'profile'
        $users = User::paginate($perPage);



        return $users;
    }

    public function enviarCorreo()
    {
        $contacts = Contact::all();
        $numero = 0;

        // Recorrer cada registro y enviar un correo
        foreach ($contacts as $contact) {
            // Puedes acceder a las propiedades de cada registro aquí
            $name = $contact->name;
            $email = $contact->email;
            $source = $contact->source;


            // Verificar si el email existe y no está vacío
            if (!empty($email)) {
                $numero++;
                // Crear una instancia de MiCorreo y pasar los datos necesarios
                $correo = new MiCorreo($name, $source);

                // Obtén la ruta completa al primer archivo PDF en storage
                $rutaPDF1 = Storage::disk('local')->path('pdf/evento_presentacion_e_invitacion_kooltivo.zip');

                // Obtén la ruta completa al segundo archivo PDF en storage
                // $rutaPDF2 = Storage::disk('local')->path('pdf/invitacion_kooltivo.pdf');

                // Adjunta el primer archivo PDF al correo
                $correo->attach($rutaPDF1, [
                    'as' => 'evento_presentacion_e_invitacion_kooltivo.zip', // Nombre del primer archivo adjunto en el correo
                    'mime' => 'application/pdf', // Tipo MIME del primer archivo adjunto
                ]);

                // Adjunta el segundo archivo PDF al correo
                // $correo->attach($rutaPDF2, [
                //     'as' => 'Invitacion_Kooltivo.pdf', // Nombre del segundo archivo adjunto en el correo
                //     'mime' => 'application/pdf', // Tipo MIME del segundo archivo adjunto
                // ]);

                // Envía el correo
                Mail::to($email)->send($correo);
            }
        }


        return $numero . ' Correos con archivos adjuntos enviados correctamente.';
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

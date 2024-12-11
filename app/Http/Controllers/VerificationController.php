<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;
use App\Mail\MailRegistro;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{

    /**
     * Store a newly created resource in storage.
     * 
     * Updated at 10/12/2024 (user)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Verification::where('email', $request->email)->first();
        if ($user) {
            $user->delete();
        }
        $code = rand(1000, 9999);
        $verification = Verification::create([
            'email' => $request->email,
            'code' => $code
        ]);
        $email = $request->email;
        $correo = new MailRegistro($request->email, $code);
        Mail::to($email)->send($correo);
        return response()->json([
            'status' => true,
            'message' => 'Envio de codigo exitoso'
        ], 200);
    }

    /**
     * Verified.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function verified(Request $request)
    {
        $user = Verification::where('email', $request->email)->first();
        if ($user->code === $request->code) {
            $user->verified = 1;
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Verificacion de codigo exitoso'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Codigo Erroneo'
            ], 400);
        }
    }
}

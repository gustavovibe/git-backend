<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;
use App\Mail\MailRegistro;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Helpers\ApiResponse;

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
    public function verified(Request $request){

        $user = Verification::where('email', $request->email)->first();
        if($user->code === $request->code){
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

    public function validateEmailReoon(Request $request){

        $email = $request->input('email');
        $apiKey2 = env('REOON_API_KEY');
        $apiKey = 'LbuThgNLnfhxiJj6sM3m9otTWDgtjmVw';
        $response = Http::get('https://emailverifier.reoon.com/api/v1/verify', [
            'email' => $email,
            'key' => $apiKey,
            'mode' => 'quick',
        ]);

        $data = $response->json();
        if($response->successful()){

            return response()->json([
                'success' => true,
                'status' => $data['status'],
                'email' => $email,
                'message' => 'Success',
                'payload' => [$request->input('email'), $apiKey, $apiKey2]
            ], 200);

        }else{
            //return ApiResponse::error( 'Email verification failed', $response->status());
            return response()->json([
                'success' => false,
                'message' => 'Email verification failed',
                'response' => $data,
                'payload' => [$request->input('email'), $apiKey, $apiKey2]
            ], 500);
        }
    }// end public function validateEmailReoon(Request $request)
}

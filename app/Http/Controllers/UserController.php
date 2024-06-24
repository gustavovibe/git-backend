<?php

namespace App\Http\Controllers;

use App\Filters\ContactFilters;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use App\Mail\ContactMail;
use App\Models\ContactEmail;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    protected $contactFilters;

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

    public function Contac(Request $r){
        DB::beginTransaction();
            try{
            $details = [
                'email'=>$r->email,
                'reference' => $r->reference,
                'trip' => $r->trip,
                'subject' => $r->subject,
                'message' => $r->message,
            ];

            $Contact = new ContactEmail();
            $Contact->fill($details)->save();
            Mail::to('adan_gonzalez@vibeadventures.com')->send(new ContactMail($details));
            DB::commit();
            return response()->json(['status'=>200,'response'=>'entro a servicio']);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }

    public function showContac(Request $r){
        try{
            //$Contact= ContactEmail::all();
            $Contact = (new ContactFilters)->ContactE($r);
            return response()->json(['status'=>200,'response'=>$Contact]);
        }catch(Exception $e){
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }
}

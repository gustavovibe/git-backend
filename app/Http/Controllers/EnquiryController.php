<?php

namespace App\Http\Controllers;

use App\Mail\EnquiryClient;
use App\Mail\EnquiryUser;
use App\Models\Enquiries;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    public function create(Request $r){
        try{
            $enquiry=Enquiries::create([
                'departure_date'=>Carbon::parse($r->departure_date),
                'name'=>$r->name,
                'last_name'=>$r->last_name,
                'email'=>$r->email,
                'phone'=>$r->phone,
                'travelers'=>$r->travelers,
                'message'=>$r->message
            ]);
             self::emailNotification($enquiry);

            Mail::to($enquiry->email)->send(new EnquiryClient($enquiry));
            return response()->json(['success'=>true,'data'=>$enquiry]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    public function emailNotification($enquiry){
        try{
            $user= User::whereHas('permission',function($query){
                $query->where('permission_id',6);
            })->get();
            foreach ($user as $u){
                Mail::to($u->email)->send(new EnquiryUser(['name'=>$u->name,'email'=>$enquiry->email]));
            }

            return $user;
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }
}

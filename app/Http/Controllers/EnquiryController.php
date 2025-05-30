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

    /**
     * create.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function create(Request $r){
        try{
            $enquiry=Enquiries::create([
                'departure_date'=>Carbon::parse($r->departure_date),
                'name'=>$r->name,
                'last_name'=>$r->last_name,
                'email'=>$r->email,
                'phone'=>$r->phone,
                'travelers'=>$r->travelers,
                'message'=>$r->message,
                'topic' => $r->topic['value'],
                'booking_id' => $r->booking,
                'adventure_link' => $r->link,
                'tour_details' => $r->tour_details,
            ]);
             self::emailNotification($enquiry);

            Mail::to($enquiry->email)->send(new EnquiryClient($enquiry));
            return response()->json(['success'=>true,'data'=>$enquiry]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    /**
     * emailNotification.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Enquiries $enquiry Enquiries object
     * @return array
     */
    public function emailNotification($enquiry){
        try{
            $user= User::whereHas('permission',function($query){
                $query->where('permission_id',6);
            })->get();
            foreach ($user as $u){
                Mail::to($u->email)->send(new EnquiryUser($enquiry));
            }

            return $user;
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Enquiries;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            return response()->json(['success'=>true,'data'=>$enquiry]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }
}

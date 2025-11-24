<?php

namespace App\Http\Controllers;

use App\Mail\EnquiryClient;
use App\Mail\EnquiryUser;
use App\Mail\EnquiryTestLisboa;
use App\Models\Enquiries;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

    /**
     * create.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function createTestLisboa(Request $r){
        try{
            Log::info('EnquiryController@createTestLisboa called', ['request' => $r->all()]);

            $enquiry = Enquiries::create([
                'departure_date'=> $r->departure_date ? Carbon::parse($r->departure_date) : null,
                'name' => $r->name,
                'last_name' => $r->last_name,
                'email' => $r->email,
                'phone' => $r->phone,
                'travelers' => $r->travelers,
                'message'=> "This is a test from Lisboa users",
                'topic' => "Lisboa users test",
                'booking_id' => null,
                'adventure_link' => null,
                'tour_details' => $r->tour_details,
            ]);

            Log::info('Enquiry created (createTestLisboa)', ['id' => $enquiry->id, 'email' => $enquiry->email]);

            if (empty($enquiry->email)) {
                Log::warning('createTestLisboa: enquiry email empty, skipping mail', ['enquiry_id' => $enquiry->id]);
                return response()->json(['success' => false, 'data' => 'Empty email for enquiry.']);
            }

            // instantiate mailable and attempt to build to capture view/render errors
            try {
                $mailable = new EnquiryTestLisboa($enquiry);

                // Try to render/build the mailable to catch view-related errors early
                try {
                    $mailable->build();
                    Log::info('createTestLisboa: mailable built successfully', ['enquiry_id' => $enquiry->id]);
                } catch (Exception $buildEx) {
                    Log::error('createTestLisboa: mailable build failed', [
                        'enquiry_id' => $enquiry->id,
                        'error' => $buildEx->getMessage(),
                        'trace' => $buildEx->getTraceAsString()
                    ]);
                    return response()->json(['success'=>false,'data'=>'Mailable build failed: '.$buildEx->getMessage()]);
                }

                // send mail
                Mail::to($enquiry->email)->send($mailable);
                Log::info('createTestLisboa: EnquiryTestLisboa mail sent', ['enquiry_id' => $enquiry->id, 'to' => $enquiry->email]);

            } catch (Exception $mailEx) {
                Log::error('createTestLisboa: error sending mail', [
                    'enquiry_id' => $enquiry->id,
                    'error' => $mailEx->getMessage(),
                    'trace' => $mailEx->getTraceAsString()
                ]);
                return response()->json(['success'=>false,'data'=>$mailEx->getMessage()]);
            }

            return response()->json(['success'=>true,'data'=>$enquiry]);

        }catch(Exception $e){
            Log::error('EnquiryController@createTestLisboa exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

}


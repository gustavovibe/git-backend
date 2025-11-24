<?php
namespace App\Http\Controllers;

use App\Filters\ContactFilters;
use App\Filters\UsersFilters;
use Illuminate\Http\Request;
use App\Models\User;
use Error;
use App\Models\Order;
use App\Models\Traveler;
use App\Helpers\ApiResponse;
use App\Mail\ContactMail;
use App\Mail\SendPass;
use App\Models\ActionLog;
use App\Models\ContactEmail;
use App\Models\ContacUs;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{


    protected $recaptchaService;

    public function __construct(RecaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }


    /**
     * Get user by id.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */ 
    public function getUserById(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'id query parameter is required.'
            ], 400);
        }

        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country' => $user->country,
            'role' => $user->role,
            'active' => $user->active,
            'suscribed' => $user->suscribed,
            'hear' => $user->hear,
            'internal_notes'=>$user->internal_notes
        ];

        return response()->json([
            'status' => true,
            'user' => $userData
        ], 200);
    }

    /**
     * Contact.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function Contac(Request $r){
        DB::beginTransaction();
            try{
            $details = [
                'link' => $r->link,
                'order' => $r->order,
                'mail_from'=>$r->mail_from,
                'mail_type' => $r->mail_type,
                'message' => $r->message,
            ];

            $Contact = new ContactEmail();
            $Contact->fill($details)->save();
            Mail::to('bookings@vibeadventures.mx')->send(new ContactMail($details));
            DB::commit();
            return response()->json(['status'=>200,'response'=>'entro a servicio']);
        }catch(Error $e){
            DB::rollback();
            return response()->json(['status'=>500,'response'=>$e]);
        }
    }

    /**
     * Show contact.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function showContac(Request $r){
        try{
            $contact =(new ContactFilters)->ContactE($r);
            return ApiResponse::success($contact);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Get users with orders.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function getUsersWithOrders(Request $r)
    {
        try{
            $users=(new UsersFilters)->UserWithOrders($r);
            return ApiResponse::success($users);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Get users orders csv.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function getUsersOrdersCsv(Request $r){
        $users= (new UsersFilters)->UserWithOrders($r);
      /*   return $users; */
          $filename = 'users_with_orders_' . now()->format('Ymd_His') . '.csv';
          $columns = ['Last booking date', 'Name', 'Country', 'Age', 'Last/Next country','Group size','Travel frequency','Total trips','Total paid','Total gross profit'];

          $callback = function () use ($users, $columns) {
              $file = fopen('php://output', 'w');
              fputcsv($file, $columns);

              foreach ($users as $user) {
                  fputcsv($file, [
                      $user['last_booking_date'],
                      $user['name'],
                      $user['country'],
                      $user['age'],
                      $user['last_booking_start_city'],
                      $user['group_size_average'],
                      $user['frequency'],
                      $user['total_orders'],
                      $user['total_paid'],
                      $user['gross_profit'],
                  ]);
              }
              fclose($file);
          };

          return response()->stream($callback, 200, [
              "Content-Type" => "text/csv",
              "Content-Disposition" => "attachment; filename=\"$filename\"",
          ]);
          return $users;
    }

    /**
     * Edit traveler.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function editTraveler(Request $r){
        try{
            $user= User::find($r->id);
            $user->fill([
                'hear'=>$r->hear,
                'suscribed'=>$r->suscribed,
                'internal_notes'=>$r->internal_notes,
            ])->save();
            return ApiResponse::success($user);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * User history.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function UserHistory(Request $r){
        try{
            $user= ActionLog::query();
            !$r->id?:$user->where('user_id',$r->id);
            $user = $user->get()->map(function ($users) {
                $users->action_date = Carbon::parse($users->action_date)->format('d M Y, g:i a');
                return $users;
            })->values()->all();

            return response()->json(['status'=>true, 'count'=>count($user),'response'=>$user]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Change password.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function changePassword(Request $r){
        try{

						$validator = Validator::make($r->all(), [
                'password' => [
									'required',
									'string',
									'min:8',
									'regex:/[a-z]/',
									'regex:/[A-Z]/',
									'regex:/[0-9]/',
									'regex:/[^\p{L}\p{N}]/u',
                ],
            ]);

            if ($validator->fails()) {

							$message = json_decode(json_encode($validator->errors()), true);
							$error_msg = [];
							foreach($message as $key => $val){
								$error_msg[] = $val[0];
							}
							$error_msg[] = 'Password must have 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character.';
							return ApiResponse::error(implode("\n", $error_msg), 200);

            }

            $user=$r->id?User::find($r->id):User::where('email',$r->email)->first();
            $user->password=Hash::make($r->password);
            $user->save();
            ActionLog::create([
                'user_id' => $user->id,
                'type' => 'Updated',
                'action' =>'Password updated successfully',
                'item' => 'User',
            ]);
            return ApiResponse::success('Change success');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }


    /**
     * Change password validation.
     * 
     * Endpoint to change password with a validation of the old password.
     * 
     * Updated at 22/03/2025 (@AaronRmz)
     * 
     * @param Request $request Request object
     * @return array  response   
     */
    public function changePasswordValidation(Request $request){
        try{
            $request->validate([
                'user_id' => 'required|integer',
                'old_password' => 'required|string|min:8',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|min:8|same:password',
            ]);

            $user = User::findOrFail($request->user_id);
            if(!Hash::check($request->old_password, $user->password)){
                return ApiResponse::error('Old password is incorrect');
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return ApiResponse::success('Password changed successfully');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Delete User.
     * 
     * Endpoint to delete user only make active = 0.
     * 
     * Updated at 22/03/2025 (@AaronRmz)
     * 
     * @param Request $request Request object
     * @return array  response   
     */
    public function deleteUser($user_id){
        DB::beginTransaction();
        try{

            $user = User::findOrFail($user_id);
            $user->active = 0;
            $user->save();

            ActionLog::create([
                'user_id' => $user_id,
                'type' => 'Delete',
                'action' =>'User deleted successfully',
                'item' => 'User',
            ]);
            DB::commit();
            return ApiResponse::success('User deleted successfully');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
            DB::rollback();
        }
    }

    /**
     * Email pass.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param int $id ID
     * @param string $password Password
     * @return array     
     */
    public function EmailPass($id,$password){
        try{
            $user= User::find($id);
            $data=[
                'password'=>$password,
                'name'=>$user->name,
                'id'=>$user->id, 
                'email'=>$user->email
            ];

            /* return view('emails.send_pass',compact('data')); */
            Mail::to($user->email)->send(new SendPass($data));
            return 'entro a pass';
        }catch(Exception $e){
            return response()->json(['success'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Send email pass.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function sendEmailPass(Request $r){
        $this->EmailPass($r->id,$r->password);
    }

    /**
     * Add contact.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array     
     */
    public function addContact(Request $r){
        try{
            $validated = $r->validate([
                'name' => 'required|string|max:255',
                'last' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'topic' => 'required',
                'message' => 'required|string',
                'booking' => 'nullable|string|max:255',  // opcional
                'link' => 'nullable|url|max:255',        // opcional
                'captchaToken' => 'required|string',
            ], [
                'name.required' => 'The name field is required.',
                'last.required' => 'The last name field is required.',
                'email.required' => 'The email field is required.',
                'email.email' => 'Please provide a valid email address.',
                'topic.required' => 'The topic field is required.',
                'message.required' => 'The message field cannot be empty.',
                'booking.required' => 'The booking field is required if provided.',
                'link.required' => 'The link field is required if provided.',
                'captchaToken.required' => 'reCAPTCHA verification is required. Please try again.',
            ]);

            $recaptchaToken = $validated['captchaToken'];
            $clientIpAddress = $r->ip();
            
            $recaptchaAssessment = $this->recaptchaService->verifyRecaptchaToken(
                $recaptchaToken,
                $clientIpAddress,
                'SUBMIT_FORM'
            );

            // Check the reCAPTCHA assessment result
             if ($recaptchaAssessment['success']) {

                $c=ContacUs::where('email',$r->email)->first();
                $contact= $c? $c:new ContacUs;
                $contact->fill($validated);
                $contact->save();

                return response()->json(['success'=>true,'data'=>$contact]);

            }else{
                // reCAPTCHA verification failed
                return response()->json(['success' => false, 'data' => [$recaptchaAssessment['message']]], 403);
            }
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = collect($errors)->flatten()->toArray();
            return response()->json(['success'=>false,'data'=>$errorMessages]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }

    }


    public function checkContact(Request $r){
        try{
            $contact = ContacUs::where('email',$r->email)->first();

            return $contact?ApiResponse::success($contact):ApiResponse::error('Not found');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    public function getContact(Request $r){
        try{
            $contact = ContacUs::where('email',$r->email)->first();
            return ApiResponse::success($contact);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }
}

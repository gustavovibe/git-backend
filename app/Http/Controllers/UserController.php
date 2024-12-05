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

class UserController extends Controller
{
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
            $contact =(new ContactFilters)->ContactE($r);
            return ApiResponse::success($contact);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    public function getUsersWithOrders(Request $r)
    {
        try{
            $users=(new UsersFilters)->UserWithOrders($r);
            return ApiResponse::success($users);
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

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

    public function changePassword(Request $r){
        try{
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

    public function EmailPass($id,$password){
        try{
            $user= User::find($id);
            $data=[
                'password'=>$password,
                'name'=>$user->name,
                'id'=>$user->id
            ];

            /* return view('emails.send_pass',compact('data')); */
            Mail::to($user->email)->send(new SendPass($data));
            return 'entro a pass';
        }catch(Exception $e){
            return response()->json(['success'=>false,'response'=>$e->getMessage()]);
        }
    }

    public function sendEmailPass(Request $r){
        $this->EmailPass($r->id,$r->password);
    }

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
            ], [
                'name.required' => 'The name field is required.',
                'last.required' => 'The last name field is required.',
                'email.required' => 'The email field is required.',
                'email.email' => 'Please provide a valid email address.',
                'topic.required' => 'The topic field is required.',
                'message.required' => 'The message field cannot be empty.',
                'booking.required' => 'The booking field is required if provided.',
                'link.required' => 'The link field is required if provided.',
            ]);
            $contact= new ContacUs;
            $contact->fill($validated);
            $contact->save();

            return response()->json(['success'=>true,'data'=>$contact]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = collect($errors)->flatten()->toArray();
            return response()->json(['success'=>false,'data'=>$errorMessages]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
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

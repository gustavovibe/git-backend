<?php

namespace App\Http\Controllers;

use App\Filters\ToursFilters;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\TourRadarController;
use App\Mail\BookingMail;
use App\Mail\SendSummary;
use App\Mail\TourDetails;
use App\Mail\AbandonedCartMail;
use App\Mail\BookAtach;
use App\Mail\BookEmail;
use App\Models\BookingSummary;
use App\Models\Order;
use App\Models\Type;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use DateInterval;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

use Exception;

class TourController extends Controller
{

    /**
     * Display a listing of tours.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function index(Request $request)
    {
        $query = Tour::query();

        if ($request->has('country')) {
            $countries = $this->extractArrayFromQueryParam($request->input('country'));
            $query->orWhereHas('countries', function ($q) use ($countries) {
                $q->whereIn('t_country_id', $countries);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('city')) {
            $cities = $this->extractArrayFromQueryParam($request->input('city'));
            $query->orWhereHas('cities', function ($q) use ($cities) {
                $q->whereIn('t_city_id', $cities);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('natural_destination')) {
            $naturalDestinations = $this->extractArrayFromQueryParam($request->input('natural_destination'));
            $query->orWhereHas('natural_destination', function ($q) use ($naturalDestinations) {
                $q->whereIn('t_natural_id', $naturalDestinations);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('tour_type')) {
            $tourType = $this->extractArrayFromQueryParam($request->input('tour_type'));
            $query->orWhereHas('type', function ($q) use ($tourType) {
                $q->whereIn('tour_type_id', $tourType);
            });
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        if ($request->has('day_price')) {
            $dayPrice = $request->input('day_price');
            $query->whereRaw('price_total / tour_length_days <= ?', [$dayPrice]);
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');

            $validSortFields = ['price_total', 'tour_length_days', 'reviews_count', 'ratings_overall', 'price_day'];

            if (in_array($sortBy, $validSortFields)) {
                if ($sortBy == 'price_day') {
                    $query->orderByRaw('price_total / tour_length_days ' . $sortOrder);
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            }
        }

        if ($request->has('tour_ids')) {
            $tourIds = $this->extractArrayFromQueryParam($request->input('tour_ids'));
            $query->whereIn('tour_id', $tourIds);
            $query->with(['cities', 'natural_destination', 'type', 'countries']);
        }

        !$request->list?:$query->select('tour_name','tour_id');

        $results = $query->get();

        return ApiResponse::success($results);
    }

    /**
     * Extract array from query param.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param string $param Param
     * @return array
     */
    protected function extractArrayFromQueryParam($param)
    {
        $param = trim($param, '[]');
        $values = explode(',', $param);
        return array_map('trim', $values);
    }

    /**
     * Get text.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public static function getText(Request $r)
    {
        $scope = "com.tourradar.bookings/read";
        $accessToken = TourRadarController::getAccessToken($scope);
        $url = "https://api.sandbox.b2b.tourradar.com/v1/operators/{$r->operatorId}";
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        try {
            $response = Http::withHeaders($headers)->get($url);
            return response()->json(['status'=>true,'response'=>$response->json()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function show(Request $r){
        try{
            $tour=ToursFilters::ToursP($r);
            return response()->json(['status'=>true,'count'=>count($tour), 'response'=>$tour]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'response'=>$e->getMessage()]);
        }
    }



    public function bookEmail(Request $r){
      /*   Mail::to('adam.g.e@outlook.com')->send(new  BookAtach());
        return 'entro'; */
        Mail::raw('Este es un correo de prueba', function ($message) {
            $message->to('adam.g.e@outlook.com')->subject('Prueba de correo');
        });
    }

    /**
     * Show type.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function show_type(Request $r){
        try{
            $travel=ToursFilters::travel_styles($r);
        /*     return $travel; */
            return response()->json(['status'=>true,'count'=>Type::count(),'response'=>$travel]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }

    /**
     * Email t details.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function emailTDetails(Request $r){
        Mail::to($r->email)->send(new TourDetails());
        return 'mail template';
    }

    /**
     * Email b confirmation.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param int $booking_id Booking ID
     * @return array
     */
    public function emailBConfirmation($tour_id,$orderId){
        try{
            $data = [
                'tour_id' => $tour_id,
                'orderId' => $orderId,
            ];

            $r = Request::create('/', 'GET', $data);

            //aqui se usa tour_id
            $orders=ToursFilters::OrdersPrint($r);

            //aqui se usa orderId
            if (empty($orderId)) {
                $booking_data = [];
            } else {
                $booking_data = (new DuffelApiController)->getOrderById($r);
                if (!isset($booking_data['data'])) {
                    $booking_data = [];
                }
            }


           $tourResponse = (new  ProxyTourRadarController)->show($orders->tour_id);
           $tourData = $tourResponse->getData(true);
           $tour=$tourData['data'];

           foreach($tour['destinations']['countries'] as $co){
               $countries[]=$co['country_name'];
           }

           foreach($tour['tour_types'] as $to){
               $tour_types[]=$to['type_name'];
           }

           foreach($tour['guide_languages'] as $text){
               $guide_types[]=$text['name'];
           }
           $countries_d=[
               'countries_text'=>implode(',',$countries),
               'tour_text'=>implode(',',$tour_types),
               'guide_text'=>implode(',',$guide_types),
           ];

           $values=['tour'=>$tour,'countries_d'=>$countries_d,'services'=>$tour['services']['included']];

            $class=[];
           if(!empty($booking_data['data'])){
               foreach ($booking_data['data']['slices'] as &$slice) {
                   foreach ($slice['segments'] as &$segment) {
                       $duration = $segment['duration'];
                       $interval = new DateInterval($duration);
                       $segment['formatted_duration'] = $interval->h . 'h ' . str_pad($interval->i, 2, '0', STR_PAD_LEFT) . 'm';
                       $segment['formatted_departing_at'] = Carbon::parse($segment['departing_at'])->format('D, d M Y, H:i');
                       $segment['formatted_departing_hour'] = Carbon::parse($segment['departing_at'])->format('H:i');
                       $segment['formatted_arriving_at'] = Carbon::parse($segment['arriving_at'])->format('D, d M Y, H:i');
                       $segment['formatted_arriving_hour'] = Carbon::parse($segment['arriving_at'])->format('H:i');
                       foreach ( $segment['passengers'] as $passengers){
                           if(!in_array($passengers['cabin_class_marketing_name'],$class)){
                               $class[]=$passengers['cabin_class_marketing_name'];
                           }
                       }
                       $segment['class']=implode(',',$class);
                   }
               }
           }

           /* return ['orders'=>$orders,'booking_data'=>$booking_data,'values'=>$values]; */
            Mail::to($orders->user->email)->send(new BookEmail($orders, $booking_data, $values));

            return ApiResponse::success('todo bien');
        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }


    }


    public function emailBookTest(Request $r){
        return $this->emailBConfirmation($r->tour_id,$r->email,$r->orderId);
    }
    /**
     * Pdf order.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
    */
    public function pdfOrder(Request $r){
        try{
            $orders=(new ToursFilters)->OrdersPrint($r);
            $orders=ToursFilters::OrdersPrint($r);

            $url_payment='';
           if($orders->payment_id){
               $client = new Client();
               $url = 'https://vibeadventures.be/api/stripe?q=' . urlencode($orders->payment_id);
               $response = $client->request('GET', $url);

               $responseBody =json_decode( $response->getBody()->getContents());
               if($responseBody->data->charge_details->receipt_url){
                $url_payment=  $responseBody->data->charge_details->receipt_url;
               }
           }

            $logo=$orders->flightTour->flight['data']['owner']['logo_symbol_url'];

            $imageContent = Http::get($logo)->body();
            $logo = 'images/logo_flight.svg'; // Ruta donde guardar la imagen

            Storage::disk('public')->put($logo, $imageContent); // Almacena la imagen en el sistema de archivos

            $logo = asset('storage/'.$logo);
           /*  return $logo; */
            $pdf = Pdf::loadView('emails.booking_confirmation_2', ['orders' => $orders,'logo'=>$logo]);
            return $pdf->stream('booking_confirmation.pdf');
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }


    public function bookingTickets(Request $r){
        try{

            $booking_data = (new DuffelApiController)->getOrderById($r);

            if (!isset($booking_data['data'])) {
                throw new Exception('Invalid booking data structure');
            }

            logger()->info('Booking data:', $booking_data);

            $class=[];

            foreach ($booking_data['data']['slices'] as &$slice) {
                foreach ($slice['segments'] as &$segment) {
                    $duration = $segment['duration'];
                    $interval = new DateInterval($duration);
                    $segment['formatted_duration'] = $interval->h . 'h ' . str_pad($interval->i, 2, '0', STR_PAD_LEFT) . 'm';
                    $segment['formatted_departing_at'] = Carbon::parse($segment['departing_at'])->format('D, d M Y, H:i');
                    $segment['formatted_departing_hour'] = Carbon::parse($segment['departing_at'])->format('H:i');
                    $segment['formatted_arriving_at'] = Carbon::parse($segment['arriving_at'])->format('D, d M Y, H:i');
                    $segment['formatted_arriving_hour'] = Carbon::parse($segment['arriving_at'])->format('H:i');
                    foreach ( $segment['passengers'] as $passengers){
                        if(!in_array($passengers['cabin_class_marketing_name'],$class)){
                            $class[]=$passengers['cabin_class_marketing_name'];
                        }
                    }
                    $segment['class']=implode(',',$class);
                }
            }

           /*  $logo=$booking_data['data']['owner']['logo_symbol_url'];

            $imageContent = Http::get($logo)->body();
            $logo = 'images/logo_flight.svg'; // Ruta donde guardar la imagen

            Storage::disk('public')->put($logo, $imageContent); // Almacena la imagen en el sistema de archivos

            $logo = asset('storage/'.$logo);
 */
            /* return $booking_data['data']; */
            $pdf = Pdf::loadView('emails.tickets_booking',['data'=>$booking_data['data']])->set_option('isRemoteEnabled', true);
            return $pdf->stream('tickets_booking.pdf');

        }catch(Exception $e){
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Booking summary send.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function bookingSummarySend(Request $r){
        try{
            Mail::to($r->email)->send(new SendSummary(['tour_id'=>$r->tour_id]));
            $summary= new BookingSummary();
            $summary->fill([
                'tour_id'=>$r->tour_id,
                'email'=>$r->email
            ])->save();
            return response()->json(['success'=>true,'data'=>$summary]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }



    /**
     * Booking summary pdf.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $r Request object
     * @return array
     */
    public function bookingSummaryPdf(Request $r){
        $tourResponse = (new  ProxyTourRadarController)->show($r->tour_id);
        $tourData = $tourResponse->getData(true);
        $tour=$tourData['data'];

        foreach($tour['destinations']['countries'] as $co){
            $countries[]=$co['country_name'];
        }

        foreach($tour['tour_types'] as $to){
            $tour_types[]=$to['type_name'];
        }

        foreach($tour['guide_languages'] as $text){
            $guide_types[]=$text['name'];
        }
        $countries_d=[
            'countries_text'=>implode(',',$countries),
            'tour_text'=>implode(',',$tour_types),
            'guide_text'=>implode(',',$guide_types),
        ];

        /* return $tour['services']['included'] ; */
        $pdf = Pdf::loadView('emails.send_summary',['tour'=>$tour,'countries_d'=>$countries_d,'services'=>$tour['services']['included'] ])->set_option('isRemoteEnabled', true);
        return $pdf->stream('booking_summary_tour.pdf');
    }

    /**
     * Carrier list.
     *
     * Updated at 10/12/2024 (user)
     *
     * @return array
     */
    public function carrierList(){
        try{
            $carriers= Order::select('carrier')->distinct()->get();
            return response()->json(['success' => true, 'data' => $carriers]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

    /**
     * Abandoned cart notification.
     *
     * Updated at 10/12/2024 (user)
     *
     * @param Request $request Request object
     * @return array
     */
    public function abandonedCartNotification(Request $request){

        $user_id = $request->has('userId') ? $request->userId : 0;
        $tour_id = $request->has('tourId') ? $request->tourId : 0;

        if (!$user_id) {
            ApiResponse::error('User Id query parameter is required.');
        }
        if (!$tour_id) {
            ApiResponse::error('Tour Id query parameter is required.');
        }

        $user = User::where('id', $id)->first();
        $tour = Tour::where('tour_id', $tour_id)->first();

        if(!$tour){
            ApiResponse::error('Tour not found.');
        }

        $tour_link = 'https://hopeful-nobel.74-208-189-166.plesk.page/tour?tourId='.$tour_id.'&departure_range=2-0&departure_fly_from=NYC&departure_fly_to=4458&adultsCount=1&childrenCount=0&infantsCount=0&dateSelected=2025/01/01-2025/01/31&totalTravelers=1';
        $emailData = [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'name' => $tour->tour_name,
            'link' => $user->country,
        ];

        Mail::to($user->email)->send(new AbandonedCartMail($emailData));

        return ApiResponse::success([], 'Email notification sent');

    }
}

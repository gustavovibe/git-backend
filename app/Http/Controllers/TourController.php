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
use App\Models\BookingSummary;
use App\Models\Order;
use App\Models\Type;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

use Exception;

class TourController extends Controller
{
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

        $results = $query->get();

        return ApiResponse::success($results);
    }

    protected function extractArrayFromQueryParam($param)
    {
        $param = trim($param, '[]');
        $values = explode(',', $param);
        return array_map('trim', $values);
    }

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

    public function show(Request $r){
        try{
            $tour=ToursFilters::ToursP($r);
            return response()->json(['status'=>true,'count'=>count($tour), 'response'=>$tour]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'response'=>$e->getMessage()]);
        }
    }

    public function show_type(Request $r){
        try{
            $travel=ToursFilters::travel_styles($r);
        /*     return $travel; */
            return response()->json(['status'=>true,'count'=>Type::count(),'response'=>$travel]);
        }catch(Exception $e){
            return response()->json(['status'=>false,'response'=>$e->getMessage()]);
        }
    }


    public function emailTDetails(Request $r){
        Mail::to($r->email)->send(new TourDetails());
        return 'mail template';
    }

    public function emailBConfirmation($booking_id){
        $b=[
            'tour_id'=>$booking_id
        ];

        request()->merge($b);

        $orders=ToursFilters::OrdersPrint(request());
        $user= User::find($orders->user_id);
        Mail::to($user->email)->send(new BookingMail($orders));
        return 'booking confirmation';
    }

    public function pdfOrder(Request $r){
        try{
            $orders=ToursFilters::OrdersPrint($r);

            $logo=$orders->flightTour->flight['data']['owner']['logo_symbol_url'];

            $imageContent = Http::get($logo)->body();
            $logo = 'images/logo_flight.svg'; // Ruta donde guardar la imagen

            Storage::disk('public')->put($logo, $imageContent); // Almacena la imagen en el sistema de archivos

            $logo = asset('storage/'.$logo);
            /* return $logo; */
            $pdf = Pdf::loadView('emails.booking_confirmation_2', ['orders' => $orders,'logo'=>$logo]);
            return $pdf->stream('booking_confirmation.pdf');
        }catch(Exception $e){
            return response()->json(['success'=>false,'data'=>$e->getMessage()]);
        }
    }

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

    public function bookingSummaryPdf(Request $r){
        $tourResponse = ProxyTourRadarController::show($r->tour_id);
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

    /*     return $tour['services']['included'] ; */
        $pdf = Pdf::loadView('emails.send_summary',['tour'=>$tour,'countries_d'=>$countries_d,'services'=>$tour['services']['included'] ])->set_option('isRemoteEnabled', true);
        return $pdf->stream('booking_summary_tour.pdf');
    }
}

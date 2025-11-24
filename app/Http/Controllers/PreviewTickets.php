<?php

namespace App\Http\Controllers;
use Illuminate\Support\Arr;
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
use App\Models\ActionLog;
use Illuminate\Support\Facades\Log;

use Exception;

class PreviewTicketsController extends Controller
{

public static  function  ticketStructure(Request $r){
        //return $r->all();
        $booking_data_response = (new DuffelApiController)->getOrderById($r);

        if ($booking_data_response instanceof \Illuminate\Http\JsonResponse) {
            $booking_data = $booking_data_response->getData(true);
        } elseif (is_array($booking_data_response)) {
            $booking_data = $booking_data_response;
        } else {
            throw new \Exception("Unexpected response type from DuffelApiController::getOrderById()");
        }

        if (!isset($booking_data['data'])) {
            throw new \Exception('Invalid booking data structure');
        }

        logger()->info('Booking data inside ticketStructure:', $booking_data);

            $passengersData = [];

            // Recorrer las "slices" y "segments" para recopilar los datos
            foreach ($booking_data['data']['slices'] as &$slice) {
                foreach ($slice['segments'] as &$segment) {

                    // Formateamos la duración y los horarios de salida y llegada
                    $duration = $segment['duration'];
                    $interval = new DateInterval($duration);
                    $segment['formatted_duration'] = $interval->h . 'h ' . str_pad($interval->i, 2, '0', STR_PAD_LEFT) . 'm';
                    $segment['formatted_departing_at'] = Carbon::parse($segment['departing_at'])->format('D, d M Y, H:i');
                    $segment['formatted_departing_hour'] = Carbon::parse($segment['departing_at'])->format('H:i');
                    $segment['formatted_arriving_at'] = Carbon::parse($segment['arriving_at'])->format('D, d M Y, H:i');
                    $segment['formatted_arriving_hour'] = Carbon::parse($segment['arriving_at'])->format('H:i');

                    // Recorrer los pasajeros y agregar la información de equipaje
                    foreach ($segment['passengers'] as &$passenger) {

                        // Verificar si los datos del pasajero están presentes
                        if (isset($passenger['title'], $passenger['given_name'], $passenger['family_name'], $passenger['born_on'], $passenger['cabin_class'])) {

                            // Crear un array de equipajes formateados
                            $baggageDetails = [];
                            foreach ($passenger['baggages'] as $baggage) {
                                $baggageDetails[] = $baggage['quantity'] . 'x ' . ucfirst($baggage['type']) . ' bag (' .
                                                ($baggage['dimensions']['length'] ?? 'N/A') . ' + ' .
                                                ($baggage['dimensions']['width'] ?? 'N/A') . ' + ' .
                                                ($baggage['dimensions']['height'] ?? 'N/A') . ' cm, ' .
                                                ($baggage['weight'] ?? 'N/A') . ' kg)';
                            }

                            // Asignamos los detalles del equipaje al pasajero
                            $passenger['baggage_details'] = implode(', ', $baggageDetails);

                            // Recopilamos la información del pasajero y el vuelo
                            $passengerData = [
                                'passenger' => [
                                    'title' => $passenger['title'] ?? 'N/A',
                                    'given_name' => $passenger['given_name'] ?? 'N/A',
                                    'family_name' => $passenger['family_name'] ?? 'N/A',
                                    'born_on' => isset($passenger['born_on']) ? Carbon::parse($passenger['born_on'])->format('d M Y') : 'N/A',
                                    'cabin_class' => $passenger['cabin_class'] ?? 'N/A',
                                ],
                                'baggage_details' => $passenger['baggage_details'] ?? 'N/A',
                                'flight_info' => [
                                    'departing_at' => $segment['formatted_departing_at'] ?? 'N/A',
                                    'arriving_at' => $segment['formatted_arriving_at'] ?? 'N/A',
                                    'flight_number' => $segment['operating_carrier_flight_number'] ?? 'N/A',
                                    'carrier' => $segment['operating_carrier']['name'] ?? 'N/A',
                                ]
                            ];

                            // Guardamos los datos del pasajero con su equipaje en el arreglo
                            $passengersData[] = $passengerData;
                        } else {
                            // Si faltan los datos del pasajero, agregamos valores por defecto
                            $passengersData[] = [
                                'passenger' => [
                                    'title' => 'N/A',
                                    'given_name' => 'N/A',
                                    'family_name' => 'N/A',
                                    'born_on' => 'N/A',
                                    'cabin_class' => 'economy',
                                ],
                                'baggage_details' => 'N/A',
                                'flight_info' => [
                                    'departing_at' => 'N/A',
                                    'arriving_at' => 'N/A',
                                    'flight_number' => 'N/A',
                                    'carrier' => 'N/A',
                                ]
                            ];
                        }
                    }
                }
            }

            return  [
                'data' => $booking_data['data'],
                'passengers_data' => $passengersData
            ];
    }
}
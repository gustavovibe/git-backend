<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Citycontroller;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\NaturalDestinationController;
use App\Http\Controllers\ReverseProxyController;
use App\Http\Controllers\TourCitiesController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourCountriesController;
use App\Http\Controllers\TourNaturalDestinationController;
use App\Http\Controllers\ProxyTourRadarController;
use App\Http\Controllers\ProxyKiwiController;
use App\Http\Controllers\DuffelApiController;

Route::post('import-cities', [Citycontroller::class, 'import']);

Route::post('import-countries', [CountryController::class, 'import']);

Route::post('import-natural_destinations', [NaturalDestinationController::class, 'import']);

Route::resource('cities', Citycontroller::class);

Route::resource('countries', CountryController::class);

Route::resource('natural_destinations', NaturalDestinationController::class);

Route::post('register', [AuthController::class, 'register']);

Route::get('location-proxy', [ReverseProxyController::class, 'proxyLocation']);

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('test', [AuthController::class, 'test']);

    Route::get('logout', [AuthController::class, 'logout']);
});

Route::get('tour/{id}', [ProxyTourRadarController::class, 'show']);

Route::get('destinations', [Citycontroller::class, 'DestinatioCityCountryNaturalDestination']);
Route::get('departures', [ProxyTourRadarController::class, 'departures']);
Route::get('departure', [ProxyTourRadarController::class, 'departure']);
Route::get('prices', [ProxyTourRadarController::class, 'prices']);
Route::get('operator-booking-fields', [ProxyTourRadarController::class, 'bookingFields']);
Route::get('bookings-list', [ProxyTourRadarController::class, 'bookingsList']);
Route::post('bookings-create', [ProxyTourRadarController::class, 'bookingsStore']);
Route::get('search-flights', [ProxyKiwiController::class, 'searchFlights']);
Route::get('check-flights', [ProxyKiwiController::class, 'checkFlights']);
Route::get('save-booking', [ProxyKiwiController::class, 'saveBooking']);
Route::get('confirm-payment', [ProxyKiwiController::class, 'confirmPayment']);
Route::get('confirm-payment-zooz', [ProxyKiwiController::class, 'confirmPaymentZooz']);

Route::resource('tour_cities', TourCitiesController::class);
Route::resource('tours', TourController::class);
Route::resource('tour_countries', TourCountriesController::class);

Route::resource('tour_natural_destinations', TourNaturalDestinationController::class);

Route::get('duffel/offer-requests', [DuffelApiController::class, 'offerRequests']);
Route::get('duffel/single-offer', [DuffelApiController::class, 'singleOffer']);
Route::get('duffel/single-request', [DuffelApiController::class, 'singleRequest']);

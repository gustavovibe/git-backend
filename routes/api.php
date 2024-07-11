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
use App\Http\Controllers\TourRadarController;
use App\Http\Controllers\ProxyKiwiController;
use App\Http\Controllers\DuffelApiController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\GustavoDuffelController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TourIdController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TravelersController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('test', [AuthController::class, 'test']);
    Route::get('logout', [AuthController::class, 'logout']);
});
Route::post('import-cities', [Citycontroller::class, 'import']);
Route::post('email-verification/code', [VerificationController::class, 'store']);
Route::post('email-verification/verified', [VerificationController::class, 'verified']);
Route::post('import-countries', [CountryController::class, 'import']);
Route::post('import-natural_destinations', [NaturalDestinationController::class, 'import']);
Route::resource('cities', Citycontroller::class);
Route::resource('countries', CountryController::class);
Route::resource('natural_destinations', NaturalDestinationController::class);
Route::post('register', [AuthController::class, 'register']);
Route::get('location-proxy', [ReverseProxyController::class, 'proxyLocation']);
Route::post('login', [AuthController::class, 'login']);
Route::get('tour/{id}', [ProxyTourRadarController::class, 'show']);
Route::get('destinations', [Citycontroller::class, 'DestinatioCityCountryNaturalDestination']);
Route::get('departures', [ProxyTourRadarController::class, 'departures']);
Route::get('departure', [ProxyTourRadarController::class, 'departure']);
Route::get('prices', [ProxyTourRadarController::class, 'prices']);
Route::get('operator-booking-fields', [ProxyTourRadarController::class, 'bookingFields']);
Route::get('bookings-list', [ProxyTourRadarController::class, 'bookingsList']);
Route::post('bookings-create', [ProxyTourRadarController::class, 'bookingsStore']);
Route::get('tour-radar-destinations', [ProxyTourRadarController::class, 'destinations']);
Route::get('search-flights', [ProxyKiwiController::class, 'searchFlights']);
Route::get('check-flights', [ProxyKiwiController::class, 'checkFlights']);
Route::get('save-booking', [ProxyKiwiController::class, 'saveBooking']);
Route::get('confirm-payment', [ProxyKiwiController::class, 'confirmPayment']);
Route::get('confirm-payment-zooz', [ProxyKiwiController::class, 'confirmPaymentZooz']);
Route::resource('tour_cities', TourCitiesController::class);
Route::resource('tours', TourController::class);
Route::resource('tour_countries', TourCountriesController::class);
Route::resource('tour_natural_destinations', TourNaturalDestinationController::class);
Route::get('duffel/create-request-get-offers', [DuffelApiController::class, 'createRequestGetOffers']);
Route::get('duffel/get-offer-by-id', [DuffelApiController::class, 'getOfferById']);
Route::get('duffel/get-request-by-id', [DuffelApiController::class, 'getRequestById']);
Route::get('/duffel-api/offer-requests', [GustavoDuffelController::class, 'offerRequests']);

Route::post('/book-package', [PackageController::class, 'createCheckoutSession']);
Route::get('filterdepartures', [TourRadarController::class, 'getMultipleDeparturesByTours']);
Route::get('/tour-ids', [TourIdController::class, 'index']);

Route::get('/travelers', [TravelersController::class, 'getTravelers']);
Route::post('/write-travelers', [TravelersController::class, 'writeTravelers']);
Route::post('/write-orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'getOrders']);
Route::get('/admin-orders', [OrderController::class, 'adminOrders']);
Route::get('/orders/{booking_id}', [OrderController::class, 'getOrderWithTravelers']);

Route::get('/users', [UserController::class, 'getUserByEmail']);

Route::post('/contact', [UserController::class, 'Contac']);
Route::get('/show-contact', [UserController::class, 'showContac']);

Route::get('/wishlists', [WishlistController::class, 'index']);
Route::get('/wishlists/{id}', [WishlistController::class, 'show']);

Route::post('/add-user', [UserController::class, 'createUser']);

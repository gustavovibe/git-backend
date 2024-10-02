<?php


use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\StripeController;
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
use App\Http\Controllers\JobsController;
use App\Http\Controllers\OperatorsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TourIdController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TravelersController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SystemUserController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('admin-destinations', DestinationController::class);
    Route::get('test', [AuthController::class, 'test']);
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('import-cities', [Citycontroller::class, 'import']);
Route::post('email-verification/code', [VerificationController::class, 'store']);
Route::post('email-verification/verified', [VerificationController::class, 'verified']);
Route::post('import-countries', [CountryController::class, 'import']);
Route::post('import-natural_destinations', [NaturalDestinationController::class, 'import']);
Route::resource('cities', Citycontroller::class);
Route::get('selection', [Citycontroller::class, 'selectiontable']);
Route::resource('countries', CountryController::class);
Route::get('get-destinations', [Citycontroller::class, 'destinations']);
Route::resource('natural_destinations', NaturalDestinationController::class);
Route::post('register', [AuthController::class, 'register']);
Route::get('location-proxy', [ReverseProxyController::class, 'proxyLocation']);
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
Route::get('show-tours', [TourController::class, 'show']);
Route::get('tours-text', [TourController::class, 'getText']);
Route::get('show-type', [TourController::class, 'show_type']);
Route::resource('tour_countries', TourCountriesController::class);
Route::resource('tour_natural_destinations', TourNaturalDestinationController::class);
Route::get('duffel/create-request-get-offers', [DuffelApiController::class, 'createRequestGetOffers']);
Route::get('duffel/get-offer-by-id', [DuffelApiController::class, 'getOfferById']);
Route::get('duffel/get-order-by-id', [DuffelApiController::class, 'getOrderById']);
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

Route::get('/users', [UserController::class, 'getUserById']);
Route::get('/users-history', [UserController::class, 'UserHistory']);
Route::post('/users-travelers', [UserController::class, 'editTraveler']);
Route::post('/users-pass', [UserController::class, 'changePassword']);

Route::post('/contact', [UserController::class, 'Contac']);
Route::get('/show-contact', [UserController::class, 'showContac']);

Route::get('/wishlists', [WishlistController::class, 'index']);
Route::get('/wishlists/{id}', [WishlistController::class, 'show']);
Route::get('/get-all-countries', [CountryController::class, 'getAllCountries']);

Route::get('/orders-all', [OrderController::class, 'index']);
Route::get('/order/{id}', [OrderController::class, 'getOrder']);
Route::get('/admin-reports', [OrderController::class, 'adminReports']);

Route::post('/add-users', [SystemUserController::class, 'createUser']);
Route::get('/get-users', [SystemUserController::class, 'getUsers']);
Route::get('/validate-email', [SystemUserController::class, 'validateEmail']);
Route::delete('/delete-users', [SystemUserController::class, 'deleteUsers']);


Route::resource('jobs', JobsController::class);
Route::resource('roles', RolesController::class);

Route::get('/traveler-data', [TravelersController::class, 'getTravelerData']);

Route::put('/travelers/{id}', [TravelersController::class, 'update']);
Route::delete('/travelers/{id}', [TravelersController::class, 'destroy']);

Route::get('/users-with-orders', [UserController::class, 'getUsersWithOrders']);
Route::resource('operators', OperatorsController::class);
Route::get('operators-import', [OperatorsController::class, 'import']);
Route::get('tours-text', [OperatorsController::class, 'text']);

Route::get('cities-c', [Citycontroller::class, 'cities']);
Route::get('countries-filter', [CountryController::class, 'getCountries']);

Route::get('/email-tour-details', [TourController::class, 'emailTDetails']);
Route::get('/email-booking-confirmation', [TourController::class, 'emailTDetails']);

Route::get('destinationsV2', [Citycontroller::class, 'destinationsV2']);

Route::get('/email-tour-details', [TourController::class, 'emailTDetails']);
Route::get('/email-booking-confirmation', [TourController::class, 'emailTDetails']);

Route::get('duffel/get-seats', [DuffelApiController::class, 'getSeats']);

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);

Route::resource('action-logs', ActionLogController::class);

Route::get('boooking-email',[TourController::class,'emailBConfirmation']);

Route::post('pass-email',[UserController::class,'sendEmailPass']);

Route::get('boooking-pdf',[TourController::class,'pdfOrder']);



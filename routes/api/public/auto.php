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
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\GustavoDuffelController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\OperatorsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\NewPackageController;
use App\Http\Controllers\TourIdController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TravelersController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SystemUserController;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PushNotificationsController;
use App\Http\Controllers\ValidatorController;
use App\Http\Controllers\NezasaController;
use Dedoc\Scramble\Scramble;
use App\Http\Controllers\PreviewMailController;
use App\Http\Controllers\PreviewInvoiceController;
use App\Http\Controllers\SnapshotController;

// ============================================================================
// AUTENTICACIÓN Y REGISTRO
// ============================================================================

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('google-register', [AuthController::class, 'googleRegister']);
Route::get('/recover-pass', [AuthController::class, 'recoverPass']);
Route::get('/check-token-pass', [AuthController::class, 'checkToken']);
Route::post('pass-email', [UserController::class, 'sendEmailPass']);

// ============================================================================
// VERIFICACIÓN DE EMAIL
// ============================================================================

Route::post('email-verification/code', [VerificationController::class, 'store']);
Route::post('email-verification/verified', [VerificationController::class, 'verified']);
Route::post('/validate-email-reoon', [VerificationController::class, 'validateEmailReoon']);

// ============================================================================
// DESTINOS Y UBICACIONES
// ============================================================================

Route::resource('cities', Citycontroller::class);
Route::resource('countries', CountryController::class);
Route::resource('natural_destinations', NaturalDestinationController::class);
Route::get('get-destinations', [Citycontroller::class, 'destinations']);
Route::get('destinationsV2', [Citycontroller::class, 'destinationsV2']);
Route::get('destinations', [Citycontroller::class, 'DestinatioCityCountryNaturalDestination']);
Route::get('get_destination_guide', [DestinationController::class, 'getDestinationGuide']);
Route::get('get_unsplash_gallery', [DestinationController::class, 'getUnsplashGallery']);
Route::get('selection', [Citycontroller::class, 'selectiontable']);
Route::get('codes', [Citycontroller::class, 'codes']);
Route::get('cities-c', [Citycontroller::class, 'cities']);
Route::get('countries-filter', [CountryController::class, 'getCountries']);
Route::get('/get-all-countries', [CountryController::class, 'getAllCountries']);
Route::post('search_youtube', [DestinationController::class, 'searchYTApi']);

// Importaciones de datos
Route::post('import-cities', [Citycontroller::class, 'import']);
Route::post('import-countries', [CountryController::class, 'import']);
Route::post('import-natural_destinations', [NaturalDestinationController::class, 'import']);

// ============================================================================
// TOURS
// ============================================================================

Route::resource('tours', TourController::class);
Route::resource('tour_cities', TourCitiesController::class);
Route::resource('tour_countries', TourCountriesController::class);
Route::resource('tour-natural-destinations', TourNaturalDestinationController::class);
Route::get('show-tours', [TourController::class, 'show']);
Route::get('show-type', [TourController::class, 'show_type']);
Route::get('carrier-list', [TourController::class, 'carrierList']);
Route::get('tours-text', [TourController::class, 'getText']);
Route::get('tour-type-list', [TourNaturalDestinationController::class, 'Type']);
Route::get('/tour-ids', [TourIdController::class, 'index']);
Route::get('filterdepartures', [TourRadarController::class, 'getMultipleDeparturesByTours']);
Route::get('filterdeparturesdb', [TourRadarController::class, 'getMultipleDeparturesOnlyDb']);

// ============================================================================
// TOURRADAR (PROXY)
// ============================================================================

Route::get('tour/{id}', [ProxyTourRadarController::class, 'show']);
Route::get('departures', [ProxyTourRadarController::class, 'departures']);
Route::get('departure', [ProxyTourRadarController::class, 'departure']);
Route::get('departuredb', [ProxyTourRadarController::class, 'departuredb']);
Route::get('prices', [ProxyTourRadarController::class, 'prices']);
Route::get('operator-booking-fields', [ProxyTourRadarController::class, 'bookingFields']);
Route::get('bookings-list', [ProxyTourRadarController::class, 'bookingsList']);
Route::post('bookings-create', [ProxyTourRadarController::class, 'bookingsStore']);
Route::get('tour-radar-destinations', [ProxyTourRadarController::class, 'destinations']);
Route::get('tourradar-status/{id}', [TourRadarController::class, 'checkBooking']);

// ============================================================================
// VUELOS - DUFFEL API
// ============================================================================

Route::get('airlines', [DuffelApiController::class, 'getAirline']);
Route::get('duffel/create-request-get-offers', [DuffelApiController::class, 'createRequestGetOffers']);
Route::get('duffel/get-offer-by-id', [DuffelApiController::class, 'getOfferById']);
Route::get('duffel/sort-offer', [DuffelApiController::class, 'getOffer']);
Route::get('duffel/get-order-by-id', [DuffelApiController::class, 'getOrderById']);
Route::get('duffel/get-request-by-id', [DuffelApiController::class, 'getRequestById']);
Route::get('duffel/get-seats', [DuffelApiController::class, 'getSeats']);
Route::get('duffel-cancel-v2', [DuffelApiController::class, 'flightCancelV2']);
Route::get('duffel-cancel-check', [DuffelApiController::class, 'flightCancel']);
Route::post('duffel-cancel-confirm', [DuffelApiController::class, 'confirmCancel']);
Route::get('/duffel-api/offer-requests', [GustavoDuffelController::class, 'offerRequests']);

// ============================================================================
// VUELOS - KIWI (PROXY)
// ============================================================================

Route::get('search-flights', [ProxyKiwiController::class, 'searchFlights']);
Route::get('check-flights', [ProxyKiwiController::class, 'checkFlights']);
Route::get('save-booking', [ProxyKiwiController::class, 'saveBooking']);
Route::get('confirm-payment', [ProxyKiwiController::class, 'confirmPayment']);
Route::get('confirm-payment-zooz', [ProxyKiwiController::class, 'confirmPaymentZooz']);

// ============================================================================
// AEROPUERTOS
// ============================================================================

Route::get('/airports', [AirportController::class, 'getAirports']);

// ============================================================================
// PAQUETES Y RESERVAS
// ============================================================================

Route::post('/book-package-v2', [NewPackageController::class, 'createCheckoutSession']);
Route::post('/checkout', [NewPackageController::class, 'checkoutWebhook']);
Route::get('status', [NewPackageController::class, 'checkBookingStatus']);

// ============================================================================
// ORDENES Y RESERVAS
// ============================================================================

Route::post('/write-orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'getOrders']);
Route::get('/admin-orders', [OrderController::class, 'adminOrders']);
Route::get('/orders/{booking_id}', [OrderController::class, 'getOrderWithTravelers']);
Route::get('/orders-all', [OrderController::class, 'index']);
Route::get('/orders-csv', [OrderController::class, 'ordersCsv']);
Route::get('/order/{id}', [OrderController::class, 'getOrder']);
Route::post('/admin-reports', [OrderController::class, 'adminReports']);

// ============================================================================
// VIAJEROS (TRAVELERS)
// ============================================================================

Route::get('/travelers', [TravelersController::class, 'getTravelers']);
Route::get('/showtravelers', [TravelersController::class, 'show']);
Route::post('/write-travelers', [TravelersController::class, 'writeTravelers']);
Route::post('travelers/update-mail-preferences/{user}', [TravelersController::class, 'updateMailPreferences']);
Route::get('/traveler-data', [TravelersController::class, 'getTravelerData']);
Route::put('/travelers/{id}', [TravelersController::class, 'update']);
Route::delete('/travelers/{id}', [TravelersController::class, 'destroy']);
Route::get('traveler_id', [TravelersController::class, 'traveler_id']);

// ============================================================================
// USUARIOS
// ============================================================================

Route::get('/users', [UserController::class, 'getUserById']);
Route::get('/users-history', [UserController::class, 'UserHistory']);
Route::post('/users-travelers', [UserController::class, 'editTraveler']);
Route::delete('/user/{user_id}', [UserController::class, 'deleteUser']);
Route::get('/users-wishlist', [UserController::class, 'getWishlist']);
Route::get('/users-with-orders', [UserController::class, 'getUsersWithOrders']);
Route::get('users-orders-csv', [UserController::class, 'getUsersOrdersCsv']);

// ============================================================================
// WISHLIST
// ============================================================================

Route::get('/wishlists-check-traveler', [WishlistController::class, 'travelerID']);
Route::get('/wishlists', [WishlistController::class, 'show']);
Route::post('/wishlists-add', [WishlistController::class, 'store']);
Route::delete('/wishlists/{wishlist_id}', [WishlistController::class, 'delete']);
Route::post('/wishlist-delete-by-tour', [WishlistController::class, 'deleteByTourId']);

// ============================================================================
// CONTACTO
// ============================================================================

Route::post('/contact', [UserController::class, 'Contac']);
Route::get('/show-contact', [UserController::class, 'showContac']);
Route::post('/add-contact', [UserController::class, 'addContact']);
Route::post('/get-contact', [UserController::class, 'getContact']);
Route::post('/check-contact', [UserController::class, 'checkContact']);

// ============================================================================
// NOTIFICACIONES PUSH
// ============================================================================

Route::post('/push_notifications_register', [PushNotificationsController::class, 'registerGravitecSub']);
Route::post('/send_push_notification', [PushNotificationsController::class, 'sendPushNotification']);
Route::post('/send_ac_notification', [TourController::class, 'abandonedCartNotification']);

// ============================================================================
// PAGOS - STRIPE
// ============================================================================

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);
Route::get('/stripe', [StripeController::class, 'getPaymentIntentFromQuery'])->name('stripe.query');

// ============================================================================
// EMAILS Y PDFs
// ============================================================================

Route::get('/email-tour-details', [TourController::class, 'emailTDetails']);
Route::get('/email-booking-confirmation', [TourController::class, 'emailTDetails']);
Route::get('/bookingconfirmation', [PreviewMailController::class, 'bookingConfirmation']);
Route::get('/bookingcancellation', [PreviewMailController::class, 'cancelConfirmation']);
Route::get('boooking-email', [TourController::class, 'emailBookTest']);
Route::get('boooking-pdf', [TourController::class, 'pdfOrder']);
Route::match(['get', 'post'], 'boooking-summary', [TourController::class, 'bookingSummarySend']);
Route::get('boooking-summary-pdf', [TourController::class, 'bookingSummaryPdf']);

// ============================================================================
// OPERADORES
// ============================================================================

Route::resource('operators', OperatorsController::class);
Route::get('operators-list', [OperatorsController::class, 'operatorsList']);
Route::get('operators-import', [OperatorsController::class, 'import']);
Route::get('tours-text', [OperatorsController::class, 'text']);

// ============================================================================
// SISTEMA Y ADMINISTRACIÓN
// ============================================================================

Route::resource('jobs', JobsController::class);
Route::resource('action-logs', ActionLogController::class);
Route::post('/add-users', [SystemUserController::class, 'createUser']);
Route::get('/get-users', [SystemUserController::class, 'getUsers']);
Route::get('/validate-email', [SystemUserController::class, 'validateEmail']);
Route::delete('/delete-users', [SystemUserController::class, 'deleteUsers']);
Route::patch('/active-desactive-user', [SystemUserController::class, 'activeDesactiveUsers']);

// ============================================================================
// NEZASA
// ============================================================================

Route::get('/get-nezasa-itinerary', [NezasaController::class, 'getItineraryTour']);
Route::get('/get-nezasa-locations', [NezasaController::class, 'getNezasaLocations']);
Route::get('/get-db-locations', [NezasaController::class, 'getLocationsFromDatabase']);

// ============================================================================
// TICKETS Y DOCUMENTOS
// ============================================================================

Route::get('/get-tickets', [TourController::class, 'bookingTickets']);

// ============================================================================
// ENQUIRIES
// ============================================================================

Route::post('/add-enquiry', [EnquiryController::class, 'create']);

// ============================================================================
// UTILIDADES Y PROXY
// ============================================================================

Route::get('location-proxy', [ReverseProxyController::class, 'proxyLocation']);
Route::get('/sanitizedfetch', [GustavoDuffelController::class, 'fetchSanitized']);
Route::get('/proxy/fetch', [GustavoDuffelController::class, 'fetch']);

// ============================================================================
// VALIDACIÓN
// ============================================================================

Route::get('/validator', [ValidatorController::class, 'validatePhone']);

// ============================================================================
// PREVIEW Y SNAPSHOTS
// ============================================================================

Route::get('snapshots', [SnapshotController::class, 'index']);
Route::get('/preview/invoice', PreviewInvoiceController::class);

// ============================================================================
// LOGS (DEBUG)
// ============================================================================

Route::get('/logs', function () {
    // Path to the Laravel log file
    $path = storage_path('logs/laravel.log');
});

Route::get('/backlog', function () {
    // Path to the Laravel log file
    $path = storage_path('logs/backlog.log');
});

Route::get('/process', function () {
    // Path to the Laravel log file
    $path = storage_path('logs/process.log');
});

Route::get('/sync_tours', function () {
    // Path to the Laravel log file
    $path = storage_path('logs/sync_tours.log');
});

Route::get('/tours_featured', function () {
    // Path to the Laravel log file
    $path = storage_path('logs/tours_featured.log');
});

// ============================================================================
// TEST Y DEBUG
// ============================================================================

Route::post('test', [Controller::class, 'Test']);

Route::get('/test-speed', function () {
    $inicio = microtime(true);

    // Respuesta rápida de prueba
    $response = response()->json(['message' => 'API rápida']);

    $fin = microtime(true);
    $tiempo = $fin - $inicio;

    return response()->json(['message' => 'API rápida', 'tiempo' => $tiempo]);
});

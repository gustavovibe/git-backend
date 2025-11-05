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

Route::resource('admin-destinations', DestinationController::class);

Route::get('test', [AuthController::class, 'test']);

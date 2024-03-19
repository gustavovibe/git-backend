<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseForUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Citycontroller;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\NaturalDestinationController;
use App\Http\Controllers\ReverseProxyController;

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

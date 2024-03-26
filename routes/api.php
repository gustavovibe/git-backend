<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Citycontroller;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\NaturalDestinationController;
use App\Http\Controllers\ReverseProxyController;
use App\Http\Controllers\ProxyTourRadarController;

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
Route::get('prices', [ProxyTourRadarController::class, 'prices']);

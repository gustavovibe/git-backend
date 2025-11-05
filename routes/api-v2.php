<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api')->group(function () {
    foreach (glob(__DIR__.'/api/public/*.php') as $file) { require $file; }

    Route::middleware('optional.auth')->group(function () {
        foreach (glob(__DIR__.'/api/semiauth/*.php') as $file) { require $file; }
    });

    Route::middleware('auth:sanctum')->group(function () {
        foreach (glob(__DIR__.'/api/auth/*.php') as $file) { require $file; }
    });

    Route::middleware(['auth:sanctum', 'can:admin'])->prefix('admin')->as('admin.')->group(function () {
        foreach (glob(__DIR__.'/api/admin/*.php') as $file) { require $file; }
    });
});

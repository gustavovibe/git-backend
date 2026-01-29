<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Carga todas las rutas públicas (sin autenticación)
    $publicPath = __DIR__ . '/api/public';
    if (is_dir($publicPath)) {
        $publicFiles = glob($publicPath . '/*.php');
        if ($publicFiles !== false) {
            foreach ($publicFiles as $file) {
                require $file;
            }
        }
    }

    // Carga rutas con autenticación opcional
    Route::middleware('optional.auth')->group(function () {
        $semiauthPath = __DIR__ . '/api/semiauth';
        if (is_dir($semiauthPath)) {
            $semiauthFiles = glob($semiauthPath . '/*.php');
            if ($semiauthFiles !== false) {
                foreach ($semiauthFiles as $file) {
                    require $file;
                }
            }
        }
    });

    // Carga rutas que requieren autenticación
    Route::middleware('auth:sanctum')->group(function () {
        $authPath = __DIR__ . '/api/auth';
        if (is_dir($authPath)) {
            $authFiles = glob($authPath . '/*.php');
            if ($authFiles !== false) {
                foreach ($authFiles as $file) {
                    require $file;
                }
            }
        }
    });

    // Carga rutas administrativas (requieren autenticación + rol admin)
    Route::middleware(['auth:sanctum', 'can:admin'])
        ->prefix('admin')
        ->as('admin.')
        ->group(function () {
            $adminPath = __DIR__ . '/api/admin';
            if (is_dir($adminPath)) {
                $adminFiles = glob($adminPath . '/*.php');
                if ($adminFiles !== false) {
                    foreach ($adminFiles as $file) {
                        require $file;
                    }
                }
            }
        });
});

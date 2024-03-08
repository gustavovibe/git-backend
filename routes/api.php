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



Route::post('import-cities', [Citycontroller::class, 'import']);

Route::post('import-countries', [CountryController::class, 'import']);

Route::resource('cities', Citycontroller::class);

Route::resource('countries', CountryController::class);

Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login']);



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('test', [AuthController::class, 'test']);
    Route::resource('users', UserController::class);
    Route::resource('courses', CourseController::class);
    Route::resource('course_registration', CourseForUserController::class);
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::resource('news', NewsletterController::class);
/*Route::get('envio', [UserController::class, 'enviarCorreo']);

Route::get('contactos', [UserController::class, 'contactosAll']);
*/
Route::resource('reports', ReportController::class);

Route::post('envio/cursos', [UserController::class, 'enviarCorreoCursos']);

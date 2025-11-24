<?php

use Illuminate\Support\Facades\Route;
use App\Models\Enquiries;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\PreviewMailController;

Route::get('/preview/bookingconfirmation', [PreviewMailController::class, 'bookingConfirmation']);

Route::get('/', function () {
    return view('emails/enquery_test_lisboa', ['data' => Enquiries::find(100)]);
});


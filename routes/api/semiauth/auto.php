<?php
use App\Http\Controllers\UserController;


Route::get('/user/data', [UserController::class, 'showTest']);
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']); // /api/auth/register
    Route::post('login',    [AuthController::class, 'login']);    // /api/auth/login

    Route::middleware('jwt.auth')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);      // /api/auth/me
        Route::post('logout',[AuthController::class, 'logout']);  // /api/auth/logout
    });

    Route::post('refresh',  [AuthController::class, 'refresh']);  // /api/auth/refresh
});

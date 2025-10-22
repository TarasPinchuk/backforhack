<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Proxy\PlacesProxyController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);         // /api/auth/register
    Route::post('login',    [AuthController::class, 'login']);            // /api/auth/login 
    Route::post('refresh',  [AuthController::class, 'refresh']);          // /api/auth/refresh

    Route::get('yandex/url',       [AuthController::class, 'yandexUrl']);        // /api/auth/yandex/url
    Route::post('yandex/exchange', [AuthController::class, 'yandexExchange']);  // /api/auth/yandex/exchange

    Route::middleware('auth:api')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);                 // /api/auth/me
        Route::post('logout',[AuthController::class, 'logout']);            // /api/auth/logout
    });
});

Route::prefix('places')
    ->withoutMiddleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {
        Route::any('{path?}', [PlacesProxyController::class, 'handle'])
            ->where('path', '.*');
    });

// интересно заметит ли этот коммент Антон Пересекин
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Proxy\PlacesProxyController;

// ===== Auth =====
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);         // /api/auth/register
    Route::post('login',    [AuthController::class, 'login']);            // /api/auth/login
    Route::post('refresh',  [AuthController::class, 'refresh']);          // /api/auth/refresh

    Route::get('yandex/url',       [AuthController::class, 'yandexUrl']);       // /api/auth/yandex/url
    Route::post('yandex/exchange', [AuthController::class, 'yandexExchange']);  // /api/auth/yandex/exchange

    Route::middleware('auth:api')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);      // /api/auth/me
        Route::post('logout', [AuthController::class, 'logout']);  // /api/auth/logout
    });
});

// ===== Proxy → FastAPI: /api/places/**  ->  http://localhost:8001/places/**
// /api/places  и /api/places/**
Route::match(['GET','POST','PUT','PATCH','DELETE','OPTIONS'], 'places', [PlacesProxyController::class, 'handle'])
    ->withoutMiddleware(['auth:api', 'throttle:api']);
Route::match(['GET','POST','PUT','PATCH','DELETE','OPTIONS'], 'places/{path}', [PlacesProxyController::class, 'handle'])
    ->where('path', '.*')
    ->withoutMiddleware(['auth:api', 'throttle:api']);

// /api/routes  и /api/routes/**
Route::match(['GET','POST','PUT','PATCH','DELETE','OPTIONS'], 'routes', [PlacesProxyController::class, 'handle'])
    ->withoutMiddleware(['auth:api', 'throttle:api']);
Route::match(['GET','POST','PUT','PATCH','DELETE','OPTIONS'], 'routes/{path}', [PlacesProxyController::class, 'handle'])
    ->where('path', '.*')
    ->withoutMiddleware(['auth:api', 'throttle:api']);

// (Опционально) Если хочешь проксировать openapi FastAPI через Laravel:
// GET /api/places-openapi  ->  http://localhost:8001/openapi.json
Route::get('places-openapi', function () {
    $base = rtrim(config('services.places.base_url'), '/'); // напр. http://localhost:8001
    $res  = \Http::timeout(5)->get($base.'/openapi.json');
    return response($res->body(), $res->status())
        ->header('Content-Type', 'application/json');
})->withoutMiddleware(['auth:api', 'throttle:api']);

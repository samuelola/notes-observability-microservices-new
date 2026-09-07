<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Presentation\Http\Controllers\Api\V1\AuthController;
use Modules\Auth\Presentation\Http\Controllers\Api\V1\HealthController;
use Modules\Auth\Presentation\Http\Controllers\Internal\UserController;

// AUTH

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('api/internal')->group(function () {
        Route::get('me', [UserController::class, 'me']);
    });

});

Route::prefix('api/v1')->group(function () {

    Route::get('/liveness', [HealthController::class, 'liveness']);
    Route::get('/readiness', [HealthController::class, 'readiness']);

    Route::middleware(['auth.correlation'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/test', [AuthController::class, 'test']);
    });
    // PROTECTED ROUTES
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

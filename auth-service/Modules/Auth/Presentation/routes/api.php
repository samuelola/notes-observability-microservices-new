<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Presentation\Http\Controllers\Api\V1\AuthController;
use Modules\Auth\Presentation\Http\Controllers\Internal\UserController;

// AUTH

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('api/internal')->group(function () {

        Route::get('me', [UserController::class, 'me']);

    });

});

Route::prefix('api/v1')->group(function () {
    //    Route::get('/metrics', [AuthController::class, 'metrics']);
    Route::middleware(['auth.correlation', 'auth.tracing'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/test', [AuthController::class, 'test']);
    });
    // PROTECTED ROUTES
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

    });

});

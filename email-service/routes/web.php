<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/liveness', [HealthController::class, 'liveness']);
Route::get('/readiness', [HealthController::class, 'readiness']);

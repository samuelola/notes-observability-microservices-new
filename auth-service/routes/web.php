<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/metrics', function () {
    return response('
            # HELP app_up
            # TYPE app_up gauge
            app_up 1
            ', 200)->header('Content-Type', 'text/plain');
});

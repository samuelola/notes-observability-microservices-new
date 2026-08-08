<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Presentation\Http\Controllers\Api\V1\NoteController;

// PROTECTED ROUTES

Route::prefix('api/v1')->group(function () {
    Route::middleware([
        'auth.service',
        'note_inactive_owner',
        'note.correlation',
    ])->group(function () {
        Route::get('/notes', [NoteController::class, 'index']);
        Route::post('/notes', [NoteController::class, 'store']);
        Route::get('/notes/{id}', [NoteController::class, 'show']);
        Route::put('/notes/{id}', [NoteController::class, 'update']);
        Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
    });
});

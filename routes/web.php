<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Invitation routes
Route::get('/accept-invite/{token}', [App\Http\Controllers\AcceptInviteController::class, 'show'])
    ->name('accept-invite');
Route::post('/accept-invite/{token}', [App\Http\Controllers\AcceptInviteController::class, 'store'])
    ->name('accept-invite.store');

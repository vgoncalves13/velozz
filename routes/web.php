<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $host = request()->getHost();

    // Check if it's the admin panel domain (local or production)
    if ($host === 'app.velozz.test' || $host === 'app.velozz.digital') {
        return redirect('/admin');
    }

    // For any tenant domain (local or production), redirect to /app
    if (str_ends_with($host, '.velozz.test') || str_ends_with($host, '.velozz.digital')) {
        return redirect('/app');
    }

    // Fallback to welcome page
    return view('welcome');
});

// Invitation routes
Route::get('/accept-invite/{token}', [App\Http\Controllers\AcceptInviteController::class, 'show'])
    ->name('accept-invite');
Route::post('/accept-invite/{token}', [App\Http\Controllers\AcceptInviteController::class, 'store'])
    ->name('accept-invite.store');

// Meta OAuth routes
Route::middleware(['auth'])->group(function () {
    Route::get('/oauth/meta/redirect', [App\Http\Controllers\MetaOAuthController::class, 'redirect'])->name('meta.oauth.redirect');
    Route::get('/oauth/meta/callback', [App\Http\Controllers\MetaOAuthController::class, 'callback'])->name('meta.oauth.callback');
});

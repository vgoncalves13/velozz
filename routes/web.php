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

// Impersonation routes
Route::middleware(['auth'])->get('/admin/impersonate/{tenantDomain}', [App\Http\Controllers\ImpersonateController::class, 'generate'])->name('admin.impersonate');
Route::middleware(['auth'])->get('/app/switch-tenant/{tenantSlug}', [App\Http\Controllers\ImpersonateController::class, 'switchTenant'])->name('client.switch-tenant');
Route::get('/app/impersonate', [App\Http\Controllers\ImpersonateController::class, 'consume'])->name('client.impersonate');

// Embedded Forms — public
Route::get('/forms/{slug}', [App\Http\Controllers\EmbeddedFormController::class, 'show'])->name('forms.show');
Route::get('/forms/{slug}/preview', [App\Http\Controllers\EmbeddedFormController::class, 'preview'])->name('forms.preview');

// Embed JS scripts
Route::get('/embed/form-{id}.js', [App\Http\Controllers\EmbeddedFormController::class, 'script'])->name('forms.embed-script');
Route::get('/embed/whatsapp-{id}.js', [App\Http\Controllers\WhatsAppWidgetController::class, 'script'])->name('whatsapp-widget.embed-script');
Route::get('/embed/whatsapp-{id}/preview', [App\Http\Controllers\WhatsAppWidgetController::class, 'preview'])->name('whatsapp-widget.preview');

// Meta OAuth routes
Route::middleware(['auth'])->group(function () {
    Route::get('/oauth/meta/redirect', [App\Http\Controllers\MetaOAuthController::class, 'redirect'])->name('meta.oauth.redirect');
    Route::get('/oauth/meta/callback', [App\Http\Controllers\MetaOAuthController::class, 'callback'])->name('meta.oauth.callback');
    Route::get('/oauth/instagram/redirect', [App\Http\Controllers\InstagramOAuthController::class, 'redirect'])->name('instagram.oauth.redirect');
    Route::get('/oauth/instagram/callback', [App\Http\Controllers\InstagramOAuthController::class, 'callback'])->name('instagram.oauth.callback');
});

// Instagram compliance routes (public, no auth)
Route::get('/data-deletion/confirm', [App\Http\Controllers\InstagramComplianceController::class, 'confirmDeletion'])->name('instagram.deletion-confirm');

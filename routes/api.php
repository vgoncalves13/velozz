<?php

use App\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\ZApiWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Cross-domain form/widget submissions (no CSRF, no auth)
Route::post('/forms/{slug}/submit', [App\Http\Controllers\EmbeddedFormController::class, 'submit'])->name('api.forms.submit');
Route::post('/widgets/whatsapp/{id}/submit', [App\Http\Controllers\WhatsAppWidgetController::class, 'submit'])->name('api.whatsapp-widget.submit');

// Z-API Webhook (public, no authentication)
Route::post('/webhook/zapi', [ZApiWebhookController::class, 'handle'])
    ->name('zapi.webhook');

// Meta Webhook (Instagram DM + Facebook Messenger, single global endpoint)
Route::get('/webhook/meta', [MetaWebhookController::class, 'verify'])
    ->name('meta.webhook.verify');
Route::post('/webhook/meta', [MetaWebhookController::class, 'receive'])
    ->name('meta.webhook.receive');

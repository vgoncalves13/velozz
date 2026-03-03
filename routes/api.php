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

// Z-API Webhook (public, no authentication)
Route::post('/webhook/zapi', [ZApiWebhookController::class, 'handle'])
    ->name('zapi.webhook');

// Meta Webhook (Instagram DM + Facebook Messenger, single global endpoint)
Route::get('/webhook/meta', [MetaWebhookController::class, 'verify'])
    ->name('meta.webhook.verify');
Route::post('/webhook/meta', [MetaWebhookController::class, 'receive'])
    ->name('meta.webhook.receive');

<?php

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

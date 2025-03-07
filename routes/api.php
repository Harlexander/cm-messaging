<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/brevo', [App\Http\Controllers\BrevoWebhookController::class, 'handleWebhook'])
    ->name('webhooks.brevo');

<?php

use App\Http\Controllers\LandbotWebhookController;
use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('test', function () {
    return response()->json(['message' => '¡Proyecto funcionando!']);
});

//Consent
Route::post('consent', [ConsetController::class, 'store']);

//Dashboard
Route::get('dashboard', [DashboardController::class, 'getMetrics']);

//Landbot
Route::post('landbot/webhook', [LandbotWebhookController::class, 'handleWebhook']);

<?php

use App\Http\Controllers\Base\BrandsController;
use App\Http\Controllers\LandbotWebhookController;
use App\Http\Controllers\Operation\ClientController;
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

//clients
Route::get('clients', [ClientController::class, 'indexPaginated']);
Route::post('clients', [ClientController::class, 'store']);
Route::put('clients/{id}', [ClientController::class, 'update']);
Route::delete('clients/{id}', [ClientController::class, 'delete']);
Route::post('clients/landbot', [ClientController::class, 'addClientLandbot']);
Route::post('clients/exist', [ClientController::class, 'existClientByPhone']);

//Brands
Route::get('brands', [BrandsController::class, 'indexPaginated']);
Route::post('brands', [BrandsController::class, 'store']);
Route::put('brands/{id}', [BrandsController::class, 'update']);
Route::delete('brands/{id}', [BrandsController::class, 'delete']);

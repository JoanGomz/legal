<?php

use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('test', function () {
    return response()->json(['message' => '¡Proyecto funcionando!']);
});

Route::post('consent', [ConsetController::class, 'store']);
Route::get('dashboard', [DashboardController::class, 'getMetrics']);

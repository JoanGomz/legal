<?php

use App\Http\Controllers\Operation\ConsetController;
use Illuminate\Support\Facades\Route;


Route::post('test', function () {
    return response()->json(['message' => '¡Proyecto funcionando!']);
});

Route::post('consent', [ConsetController::class, 'store']);

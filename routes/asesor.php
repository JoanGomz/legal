<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'panels.asesor.dashboard')->name('dashboard');
    Route::livewire('/chat', 'panels.asesor.conversation_hub')->name('chat');
});

<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::livewire('/', 'pages.auth.login')
    ->name('login');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('profile', 'profile')
        ->middleware(['auth'])
        ->name('profile');

    Route::livewire('dashboard', 'panels.dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::prefix('accesos')->group(function () {
        Route::livewire('usuarios', 'panels.identity-management.users-directory')
            ->name('access.users');
        Route::livewire('roles', 'panels.identity-management.roles')
            ->name('access.roles');
        Route::livewire('permisos', 'panels.identity-management.permissions')
            ->name('access.permissions');
    });
});


require __DIR__ . '/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.auth.login')
    ->name('login');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('profile', 'profile')
        ->middleware(['auth'])
        ->name('profile');

    Volt::route('dashboard', 'panels.dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::prefix('accesos')->group(function () {
        Volt::route('usuarios', 'panels.identity-management.users-directory')
            ->name('access.users');
        Volt::route('roles', 'panels.identity-management.roles')
            ->name('access.roles');
        Volt::route('permisos', 'panels.identity-management.permissions')
            ->name('access.permissions');
    });
});


require __DIR__ . '/auth.php';

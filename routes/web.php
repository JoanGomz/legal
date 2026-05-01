<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::livewire('/', 'pages.auth.login')
    ->name('login');
Route::livewire('/consentimiento', 'panels.consent-form.consent-atracction')
    ->name('consent');
Route::livewire('/eventsconsent', 'panels.consent-form.event-consent')
    ->name('consent.events');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('profile', 'profile')
        ->name('profile');

    Route::prefix('sedes')->group(function () {
        Route::livewire('dependencias', 'panels.locations.branches')
            ->name('branches');
        Route::livewire('Atracciones', 'panels.locations.atracctions')
            ->name('atracctions');
    });

    Route::livewire('dashboard', 'panels.dashboard')
        ->name('dashboard');

    Route::livewire('admin-consent', 'panels.admin-consent.consent')
        ->name('Consents');

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

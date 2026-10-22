<?php

use App\Http\Controllers\PushController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectByRole;

Route::post('/push/subscribe', [PushController::class, 'subscribe'])->middleware('auth');
Route::livewire('/', 'pages.auth.login')->name('login');
Route::livewire('/consentimiento', 'panels.consent-form.consent-atracction')->name('consent');
Route::livewire('/consentimiento/jumpingland', 'panels.consent-form.consent-form-external')->name('consent-external');
Route::livewire('/eventsconsent', 'panels.consent-form.event-consent')->name('consent.events');

Route::middleware(['auth', 'verified'])
    ->prefix('asesor')
    ->name('asesor.')
    ->group(__DIR__ . '/asesor.php');


Route::middleware(['auth', 'verified', RedirectByRole::class])->group(function () {
    Route::view('profile', 'profile')->name('profile');

    Route::prefix('sedes')->group(function () {
        Route::livewire('dependencias', 'panels.locations.branches')->name('branches');
        Route::livewire('atracciones', 'panels.locations.atracctions')->name('atracctions');
    });

    Route::livewire('dashboard', 'panels.dashboard')->name('dashboard');
    Route::livewire('admin-consent', 'panels.admin-consent.consent')->name('Consents');

    Route::prefix('accesos')->group(function () {
        Route::livewire('usuarios', 'panels.identity-management.users-directory')->name('access.users');
        Route::livewire('roles', 'panels.identity-management.roles')->name('access.roles');
        Route::livewire('permisos', 'panels.identity-management.permissions')->name('access.permissions');
    });
});

use App\Livewire\Actions\Logout;

Route::post('logout', function (Logout $logout) {
    $logout();
    return redirect('/');
})->middleware('auth')->name('logout');
require __DIR__ . '/auth.php';

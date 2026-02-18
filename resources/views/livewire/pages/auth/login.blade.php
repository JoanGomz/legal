<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <div>
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl overflow-hidden shadow-xl">
                <div class="flex p-8 items-center justify-center flex-col">
                    <img width="90 px" height="90 px" src=" images/spoon-negro.png " alt="Logo de Spoon de Colombia">
                    <span class="text-color-black m-4"> Ingresa tus credenciales</span>
                    <div class="w-full">
                        <input
                            class="order-gray-300 w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm"
                            wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email"
                            placeholder="Correo Electronico" required autofocus autocomplete="username" />

                    </div>

                    <!-- Password -->
                    <div class="w-full mt-4">

                        <input
                            class="border-slate-700 w-full focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm"
                            wire:model="form.password" id="password" class="block mt-1 w-full" type="password"
                            name="password" required autocomplete="current-password" placeholder="Contraseña" />

                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>
                    <div class="w-full mt-6">
                        <button class="animate-pulse rounded-xl w-full bg-slate-500 h-14 text-white font-bold">
                            {{ __('Iniciar sesión') }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
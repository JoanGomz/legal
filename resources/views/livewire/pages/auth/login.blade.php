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

<div class="w-full min-h-screen bg-slate-800 flex items-center justify-center">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="w-full max-w-5xl">
        <div
            class="flex flex-col md:flex-row w-full backdrop-blur-xl overflow-hidden shadow-2xl shadow-slate-700/100  items-stretch">

            <div class="flex w-full bg-slate-950 rounded-ee-3xl rounded-se-3xl justify-center p-8 lg:p-12">
                <div class="flex flex-col items-center justify-center h-full">
                    <img width="80" src="images/spoon-trasp.png" alt="Logo de Spoon de Colombia" class="mb-6">

                    <h2 class="text-white text-2xl font-semibold mb-2">Bienvenido</h2>
                    <p class="text-slate-400 text-sm mb-8 text-center">Ingresa tus credenciales para continuar</p>

                    <div class="w-full space-y-5">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-envelope text-blue-400/60"></i>
                            </div>
                            <input wire:model="form.email" type="email" required placeholder="Correo Electrónico"
                                class="w-full pl-10 pr-4 py-4 bg-slate-900/50 text-white placeholder-slate-500 rounded-xl border border-white/5 focus:ring-2 focus:ring-blue-500/50 focus:border-transparent outline-none transition-all">
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-blue-400/60"></i>
                            </div>
                            <input wire:model="form.password" type="password" required placeholder="Contraseña"
                                class="w-full pl-10 pr-4 py-4 bg-slate-900/50 text-white placeholder-slate-500 rounded-xl border border-white/5 focus:ring-2 focus:ring-blue-500/50 focus:border-transparent outline-none transition-all">
                        </div>

                        <x-input-error :messages="$errors->get('form.password')" />

                        <button type="submit" wire:loading.attr="disabled" wire:loading.class=" cursor-progress"
                            class="w-full bg-[#111a2c]  hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/20 transition-all active:scale-[0.98] flex justify-center items-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Iniciar Sesión</span>
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="flex w-[800px] bg-slate-950/90 p-8 lg:p-12 flex-col items-center justify-center text-center relative overflow-hidden ">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <h2 class="text-white text-3xl font-bold mb-4 uppercase tracking-tighter">Área Jurídica</h2>
                    <div class="w-full h-1 bg-blue-400 mx-auto mb-6"></div>

                    <p class="text-blue-100 text-lg leading-relaxed mb-8">
                        Revisa los consentimientos que han sido diligenciados y entre otras cosas
                    </p>

                    <div
                        class="flex bg-slate-800/10 backdrop-blur-md p-4 rounded-2xl justify-center items-center border border-slate-400">
                        <img class="justify-center" src="images/STARP.webp" alt="Imagen de Star Park">
                    </div>
                </div>


            </div>

        </div>
    </form>
</div>
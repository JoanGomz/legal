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
@push('styles')
<style>
.wave-wrapper {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 180px;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}

.wave-container {
    display: flex;
    position: absolute;
    bottom: 0;
    width: 100%;
    /* 1440px * 2 */
    height: 100%;
    margin: 0;
    padding: 0;
}

/* ONDA FRONTAL: Hacia la izquierda */
.wave-container.front {
    left: 0;
    animation: wave-left 20s linear infinite !important;
}

/* ONDA TRASERA: Hacia la derecha */
.wave-container.back {
    opacity: 0.4;
    bottom: 50px;
    left: -100%;
    /* Empezamos desplazados para el bucle derecho */
    animation: wave-right 15s linear infinite !important;
}

@keyframes wave-left {
    0% {
        transform: translate3d(0, 0, 0);
    }

    100% {
        transform: translate3d(-100%, 0, 0);
    }
}

@keyframes wave-right {
    0% {
        transform: translate3d(0, 0, 0);
    }

    100% {
        transform: translate3d(100%, 0, 0);
    }
}

.wave-container svg {
    width: 100%;
    height: 100%;
    display: block;
    flex-shrink: 0;
}
</style>
@endpush
<div class="w-full min-h-screen bg-slate-950 flex items-center justify-center">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="w-full max-w-5xl">
        <div
            class="flex flex-col md:flex-row w-full backdrop-blur-xl overflow-hidden shadow-lg shadow-slate-700/100  items-stretch">

            <div class="flex w-full bg-slate-800/100 rounded-ee-3xl rounded-se-3xl justify-center p-8 lg:p-12">
                <div class="flex flex-col w-full items-center justify-center h-full">
                    <img width="80" height="80" src="images/spoon-trasp.webp" alt="Logo de Spoon de Colombia"
                        class="mb-6">

                    <h2 class="text-white text-2xl font-semibold mb-2">Bienvenido</h2>
                    <p class="text-white forn-bold text-sm mb-8 text-center">Ingresa tus credenciales para continuar</p>

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
                            class="w-full bg-[#111a2c]  hover:bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/20 transition-all active:scale-[0.98] flex justify-center items-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Iniciar Sesión</span>
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="flex w-[800px] bg-slate-950/100 p-8 lg:p-12 flex-col items-center justify-center text-center relative overflow-hidden ">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <h2 class="text-white text-3xl font-bold mb-4 uppercase tracking-tighter">Área Jurídica</h2>
                    <div class="w-full h-1 bg-blue-400 mx-auto mb-6"></div>

                    <p class="text-blue-100 text-lg mb-8">
                        Sección para resguardar consentimientos informados, proporcionando prueba legal y seguridad
                        frente a riesgos operativos o legales.
                    </p>

                    <div
                        class="flex bg-slate-800/10 backdrop-blur-md p-4 rounded-2xl justify-center items-center border border-slate-400">
                        <img height="180 px" width="180 px" class="justify-center" src="images/STARP.webp"
                            alt="Imagen de Star Park">
                    </div>
                </div>


            </div>

        </div>
        <div class="wave-wrapper" wire:ignore>
            <div class="wave-container back">
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#0f172a" d="M0,160 C320,300 420,20 720,160 C1020,300 1120,20 1440,160 V320 H0 Z"></path>
                </svg>
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#0f172a" d="M0,160 C320,300 420,20 720,160 C1020,300 1120,20 1440,160 V320 H0 Z"></path>
                </svg>
            </div>

            <div class="wave-container front" wire:ignore>
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#1e293b" d="M0,160 C360,350 500,10 720,160 C940,310 1080,10 1440,160 V320 H0 Z"></path>
                </svg>
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#1e293b" d="M0,160 C360,350 500,10 720,160 C940,310 1080,10 1440,160 V320 H0 Z"></path>
                </svg>
            </div>
        </div>
    </form>
</div>
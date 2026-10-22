<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('spoon-ico.ico') }}" type="image/x-icon">
    <title>{{ $title ?? config('app.name', 'Área Jurídica') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <script defer src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.ably.com/lib/ably.min-2.js"></script>
    <script>
    window.ablyApiKey = "{{ config('services.ably.key') }}";
    window.vapidKey = "{{ config('webpush.vapid.public_key') }}";
    </script>
    <style>
    [x-cloak] {
        display: none !important;
    }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/chart.js'])
    @livewireStyles
</head>

@php
$user = auth()->user();
// Parque con logo propio (Jumpingland)
$isJumpingland = (int) $user->park_id === 13;
@endphp

<body x-data="{ sidebarOpen: false }" class="bg-[#F9FAFF] font-['Figtree',sans-serif] antialiased">


    <aside
        class="flex flex-col bg-slate-950 text-white fixed inset-y-0 left-0 z-50 transition-all duration-300 shadow-2xl border-r border-slate-800"
        :class="sidebarOpen
            ? 'w-64 translate-x-0'
            : 'w-64 -translate-x-full md:translate-x-0 md:w-[72px]'">

        <!-- Encabezado del sidebar -->
        <div class="flex items-center h-16 border-b border-slate-800 bg-slate-900/50 shrink-0">
            <div class="flex items-center w-full px-4 overflow-hidden">
                <button @click="sidebarOpen = !sidebarOpen" aria-label="Abrir o cerrar menú"
                    class="p-2 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white transition-colors text-slate-400">
                    <span class="hidden md:inline-block">
                        <i class="fa-solid" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                    </span>
                    <span class="inline-block md:hidden">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                </button>

                <div x-cloak x-show="sidebarOpen" x-transition.opacity.duration.300ms
                    class="flex items-center ml-3 overflow-hidden">
                    <span class="text-md font-bold tracking-tight whitespace-nowrap">
                        Área <span class="text-blue-400">Jurídica</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Menú -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden">
            <ul class="py-6 px-3 space-y-2">

                <!-- Dashboard -->
                <li class="relative group">
                    <a href="{{ route('asesor.dashboard') }}" wire:navigate class="flex items-center h-12 rounded-xl transition-all duration-200
                        {{ request()->routeIs('asesor.dashboard')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                            : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                        <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                            <i class="fa-solid fa-chart-pie text-xl"></i>
                        </div>
                        <span x-cloak x-show="sidebarOpen" x-transition.opacity
                            class="ml-1 text-sm font-semibold whitespace-nowrap">
                            Dashboard
                        </span>
                    </a>

                    <div x-cloak x-show="!sidebarOpen"
                        class="fixed left-20 ml-2 px-3 py-1 bg-slate-900 text-white text-[11px] font-bold rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-[100] border border-slate-700 pointer-events-none uppercase tracking-wider">
                        Dashboard
                    </div>
                </li>

                <!--Chat-->
                <li class="relative group">
                    <a href="{{ route('asesor.chat') }}" wire:navigate class="flex items-center h-12 rounded-xl transition-all duration-200
                        {{ request()->routeIs('asesor.chat')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                            : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                        <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                            <i class="fa-solid fa-comments text-lg"></i>
                        </div>
                        <span x-cloak x-show="sidebarOpen" x-transition.opacity.duration.300ms
                            class="ml-1 text-sm font-semibold whitespace-nowrap">
                            Chat </span>
                    </a>

                    <div x-cloak x-show="!sidebarOpen"
                        class="fixed left-20 ml-2 px-3 py-1 bg-slate-900 text-white text-[11px] font-bold rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-[100] border border-slate-700 pointer-events-none uppercase tracking-wider">
                        Chat </div>
                </li>
            </ul>
        </nav>

        <!-- Pie del sidebar -->
        <div class="flex flex-col items-center justify-center p-4 border-t border-slate-800 shrink-0 bg-slate-950 mt-4">
            <img class="w-10 transition-transform duration-300" :class="sidebarOpen ? 'scale-110' : 'scale-90'"
                src="{{ asset('images/spoon-logo.png') }}" alt="Logo Spoon">

            <div x-cloak x-show="sidebarOpen" class="flex flex-col items-center overflow-hidden w-full"
                x-transition:enter="transition ease-out duration-300 delay-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="flex items-center bg-slate-900/50 rounded-xl px-3 h-8 mt-3 border border-slate-800/50">
                    <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap tracking-wider uppercase">
                        Spoon de Colombia
                    </span>
                </div>
                <p class="text-[9px] text-slate-600 mt-2 whitespace-nowrap">
                    © {{ now()->year }} Todos los derechos reservados
                </p>
            </div>
        </div>
    </aside>

    <!-- Fondo oscuro en móvil cuando el sidebar está abierto -->
    <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>

    <div class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'md:pl-64' : 'md:pl-[72px]'">

        <!-- Barra superior -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-30 w-full">
            <div class="px-4">
                <div class="flex justify-between h-16">

                    <div class="flex items-center gap-3">
                        <!-- Abrir menú (solo móvil) -->
                        <button x-cloak x-show="!sidebarOpen" @click="sidebarOpen = true" aria-label="Abrir menú"
                            class="md:hidden p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white transition-colors">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <div class="shrink-0 flex items-center">
                            @if ($isJumpingland)
                            <img src="https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1791921261_jumpingland.png"
                                alt="Logo del comercio" class="w-20 h-auto">
                            @else
                            <img src="{{ asset('images/logohori.png') }}" alt="Logo del comercio" class="w-40 h-auto">
                            @endif
                        </div>

                        @isset($title)
                        <div class="hidden md:flex items-center text-gray-400">
                            <span class="mx-2">/</span>
                            <h2 class="font-semibold text-gray-700">{{ $title }}</h2>
                        </div>
                        @endisset
                    </div>

                    <!-- Menú de usuario -->
                    <div class="flex items-center gap-4">
                        <x-dropdown align="right" width="56">
                            <x-slot name="trigger">
                                <button
                                    class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-100 transition-all">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm uppercase">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <p class="text-xs font-bold text-gray-800 leading-none">{{ $user->name }}</p>
                                        <p class="text-[10px] text-gray-500 leading-tight">
                                            {{ $user->roles->first()?->name ?? 'Sin rol' }}
                                        </p>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 mr-2"></i>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-400">Sesión iniciada como</p>
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $user->email }}</p>
                                </div>

                                <x-dropdown-link :href="route('profile')" wire:navigate>
                                    <i class="fa-solid fa-user-gear mr-2 text-gray-400"></i> Mi perfil
                                </x-dropdown-link>

                                <div class="border-t border-gray-100"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full px-4 py-2 text-start text-sm leading-5 text-red-600 hover:bg-red-50 transition">
                                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar sesión
                                    </button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido del componente -->
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>
    @include('components.loading-notification')
</body>

</html>
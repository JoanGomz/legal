<aside
    class="flex flex-col bg-slate-950 text-white fixed inset-y-0 left-0 z-50 transition-all duration-300 shadow-2xl border-r border-slate-800"
    :class="{
        'w-64 translate-x-0': sidebarOpen,
        '-translate-x-full md:translate-x-0 md:w-[72px]': !sidebarOpen
    }">

    <div class="flex items-center h-16 border-b border-slate-800 bg-slate-900/50 shrink-0">
        <div class="flex items-center w-full px-4 overflow-hidden">
            <button @click="sidebarOpen = !sidebarOpen" aria-label="Abrir o cerrar menu"
                class="p-2 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white transition-colors text-slate-400">

                <span class="hidden md:inline-block">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                </span>

                <span class="inline-block md:hidden">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-xmark' : 'fa-outdent'"></i>
                </span>
            </button>
            <div x-show="sidebarOpen" x-transition.opacity.duration.300ms
                class="flex items-center ml-3 overflow-hidden">
                <span class="text-md font-bold tracking-tight whitespace-nowrap">
                    Área <span class="text-blue-400">Jurídica</span>
                </span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-slate-700">
        <ul class="py-6 px-3 space-y-2">

            <li class="relative group">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex items-center h-12 rounded-xl transition-all duration-200
                    {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">

                    <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                        <i class="fa-solid fa-chart-pie text-xl"></i>
                    </div>

                    <span x-show="sidebarOpen" x-transition.opacity class="font-medium whitespace-nowrap text-sm">
                        {{ __('dashboard') }}
                    </span>
                </a>

                <div x-show="!sidebarOpen"
                    class="fixed left-20 ml-2 px-3 py-1 bg-slate-800 text-white text-xs rounded shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-[100] border border-slate-700">
                    {{ __('dashboard') }}
                </div>
            </li>

            <div class="pt-4 pb-2">
                <div x-show="sidebarOpen" class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    Gestión
                </div>
                <div x-show="!sidebarOpen" class="mx-auto w-6 border-b border-slate-800"></div>
            </div>
            <li class="relative group">
                <a href="{{ route('access.users') }}" wire:navigate
                    class="flex items-center h-12 rounded-xl transition-all duration-300
            {{ request()->routeIs('access.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">

                    <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                        <i
                            class="fa-solid fa-user-shield text-lg {{ request()->routeIs('access.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
                    </div>

                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms
                        class="ml-1 text-sm font-semibold whitespace-nowrap">
                        Control de Acceso
                    </span>
                </a>

                <div x-cloak x-show="!sidebarOpen"
                    class="fixed left-20 ml-2 px-3 py-1 bg-slate-900 text-white text-[11px] font-bold rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-[100] border border-slate-700 pointer-events-none uppercase tracking-wider">
                    Acceso
                </div>
            </li>
            <li class="relative group">
                <a href="{{ route ('branches') }}" wire:navigate
                    class="flex items-center h-12 rounded-xl transition-all duration-300
                {{ request()->routeIs('#') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">

                    <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                        <i
                            class="fa-solid fa-building text-lg {{ request()->routeIs('branches') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
                    </div>

                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms
                        class="ml-1 text-sm font-semibold whitespace-nowrap">
                        Sedes
                    </span>
                </a>

                <div x-cloak x-show="!sidebarOpen"
                    class="fixed left-20 ml-2 px-3 py-1 bg-slate-900 text-white text-[11px] font-bold rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-[100] border border-slate-700 pointer-events-none uppercase tracking-wider">
                    Sedes
                </div>
            </li>
            <li class="relative group">
                <a href="#"
                    class="flex items-center h-12 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 transition-all">
                    <div class="w-12 h-12 shrink-0 flex items-center justify-center">
                        <i class="fa-solid fa-file-signature text-lg"></i>
                    </div>
                    <span x-show="sidebarOpen" x-transition.opacity class="ml-1 font-medium whitespace-nowrap text-sm">
                        Nuevas Firmas
                    </span>
                </a>
            </li>
        </ul>
    </nav>

    <div
        class="flex flex-col items-center justify-center p-4 border-t border-slate-800 shrink-0 bg-slate-950 transition-all duration-300">
        <img class="w-10 transition-transform duration-300" :class="sidebarOpen ? 'scale-110' : 'scale-90'"
            src="{{ asset('images/spoon-logo.png') }}" alt="Logo Spoon">

        <div class="flex flex-col items-center overflow-hidden w-full" x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300 delay-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-center bg-slate-900/50 rounded-xl px-3 h-8 mt-3 border border-slate-800/50">
                <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap tracking-wider uppercase">
                    Spoon de Colombia
                </span>
            </div>

            <p class="text-[9px] text-slate-600 mt-2 whitespace-nowrap">
                © 2026 Todos los derechos reservados
            </p>
        </div>
    </div>
</aside>
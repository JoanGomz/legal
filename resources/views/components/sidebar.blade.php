<aside
    class="flex flex-col bg-slate-950 text-white fixed inset-y-0 left-0 z-50 transition-all duration-300 shadow-2xl border-r border-slate-800"
    :class="sidebarOpen ? 'w-64' : 'w-20'">

    <div class="flex items-center h-16 border-b border-slate-800 bg-slate-900/50 shrink-0">
        <div class="flex items-center w-full px-4 overflow-hidden">
            <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white transition-colors text-slate-400">
                <i class="fa-solid" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
            </button>

            <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="flex items-center ml-3 overflow-hidden">
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
                        <i class="fa-solid fa-chart-pie text-lg"></i>
                    </div>

                    <span x-show="sidebarOpen" x-transition.opacity class="ml-1 font-medium whitespace-nowrap text-sm">
                        {{ __('dashboard') }}
                    </span>
                </a>

                <div x-show="!sidebarOpen" class="fixed left-20 ml-2 px-3 py-1 bg-slate-800 text-white text-xs rounded shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-[100] border border-slate-700">
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
                <a href="#" class="flex items-center h-12 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 transition-all">
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

    <div class="p-4 border-t border-slate-800 shrink-0 bg-slate-950">
        <div class="flex items-center bg-slate-900/50 rounded-xl p-2 h-10 overflow-hidden" :class="sidebarOpen ? 'px-3' : 'justify-center px-0'">
            <i class="fa-solid fa-shield-halved text-blue-500 shrink-0"></i>
            <span x-show="sidebarOpen" x-transition.opacity class="text-[10px] text-slate-500 ml-3 font-mono whitespace-nowrap">
                StarPark Legal
            </span>
        </div>
    </div>
</aside>

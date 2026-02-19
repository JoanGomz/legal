<nav x-data="{ open: false }" class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40 w-full">
    <div class="mx-auto px-4">
        <div class="flex justify-between h-16">

            <div class="flex items-center">
                <button x-cloak @click="sidebarOpen = !sidebarOpen" x-show="!sidebarOpen" aria-label="Abrir o cerrar menu"
                    class="md:hidden p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white transition-colors">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
                <div class="shrink-0 flex items-center ">
                    <img src="{{ asset('images/logohori.png') }}" alt="StarPark Legal" class="w-40 h-auto">
                </div>

                <div class="hidden md:flex items-center ml-4 text-gray-400">
                    <span class="mx-2">/</span>
                    <h2 class="capitalize font-semibold text-gray-700">
                        {{ __(request()->route()->getName()) }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-4">


                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-100 transition-all">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-bold text-gray-800 leading-none">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-gray-500 leading-tight">Gestor Jurídico</p>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 mr-2"></i>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs text-gray-400">Sesión iniciada como</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            <i class="fa-solid fa-user-gear mr-2 text-gray-400"></i> {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <button wire:click="logout" class="w-full">
                            <x-dropdown-link class="text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i> {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>

<?php

use App\Http\Controllers\Base\BrandsController;
use App\Livewire\Actions\Logout;
use App\Traits\WithBrandFilter;
use Livewire\Volt\Component;

new class extends Component
{
    use WithBrandFilter;

    public function select(int $id): void
    {
        abort_unless(auth()->user()->hasRole('SuperAdmin'), 403);

        session(['brand_id' => $id]);
        $this->brand = $id;

        $this->dispatch('brand-changed', brandId: $id);
    }

    public function with(): array
    {
        $brands = auth()->user()->hasRole('SuperAdmin')
            ? (app(BrandsController::class)->indexPaginated(1, 50, '')['data'] ?? [])
            : [];

        $active = collect($brands)->firstWhere('id', $this->brand);

        return [
            'brands'       => $brands,
            'activeName'   => $active['name'] ?? null,
            'isSuperAdmin' => auth()->user()->hasRole('SuperAdmin'),
        ];
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
// Ajusta los ids a los reales de tu tabla de marcas
$logos = [
1 => asset('images/logohori.png'),
2 => 'https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1791921261_jumpingland.png',
];

$userName = auth()->user()->name;
@endphp

<nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40 w-full">
    <div class="mx-auto px-4">
        <div class="flex justify-between items-center h-16 gap-4">

            {{-- Izquierda: menú móvil + logo --}}
            <div class="flex items-center gap-3">
                <button x-cloak type="button" @click="sidebarOpen = !sidebarOpen" aria-label="Abrir o cerrar menú"
                    class="md:hidden p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white transition-colors">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-xmark' : 'fa-bars'"></i>
                </button>

                <div class="shrink-0 flex items-center">
                    @if (isset($logos[$brand]))
                    <img src="{{ $logos[$brand] }}" alt="Logo de la marca" class="h-10 w-auto">
                    @else
                    <span class="font-bold text-slate-800">Área Legal</span>
                    @endif
                </div>
            </div>

            {{-- Derecha: marca activa + usuario --}}
            <div class="flex items-center gap-4">
                <livewire:panels.brand-switcher />

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button"
                            class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-100 transition-all">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                {{ mb_strtoupper(mb_substr($userName, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-bold text-gray-800 leading-none">{{ $userName }}</p>
                                <p class="text-[10px] text-gray-500 leading-tight">
                                    {{ auth()->user()->roles->first()?->name ?? 'Sin Rol' }}
                                </p>
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

                        <button type="button" wire:click="logout"
                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-red-600 hover:bg-red-50 focus:outline-none focus:bg-red-50 transition duration-150 ease-in-out">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i> {{ __('Cerrar Sesión') }}
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
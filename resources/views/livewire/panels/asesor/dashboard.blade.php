<?php

use App\Http\Controllers\Operation\ClientController;
use App\Http\Controllers\Operation\DashboardController;
use App\Traits\traitCruds;
use Livewire\Volt\Component;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

new #[Layout('layouts.sidebar-asesor')] class extends Component {
    use traitCruds;
    public $start_date;
    public $end_date;
    public $brand;
    public $park;

    public function mount()
    {
        $user = auth()->user();

        $this->brand = $user->brand_id;
        $this->park = $user->park_id;
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');

        $this->end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
    }
    public function render(): mixed
    {
        $users = app(ClientController::class)->indexPaginated($this->page, $this->perPage, $this->search, (int) $this->brand, (int) $this->park);
        $data = app(DashboardController::class)->getAllMetrics($this->start_date, $this->end_date);
        $park_data = app(DashboardController::class)->getMetricsByPark($this->start_date, $this->end_date);

        return view('livewire.panels.asesor.dashboard', [
            'data' => $data,
            'park_data' => $park_data,
            'users' => $users
        ]);
    }
}; ?>

<div class="py-4">
    @php
    $user = Auth::user();
    @endphp
    <div x-data="{filter:false}" class="space-x-4 mx-auto px-4">
        <div class="flex justify-between p-4 bg-white shadow sm:rounded-lg">
            <h1 class="text-xl font-semibold text-gray-900">
                Bienvenid@ <a href="{{ route('profile') }}" class="text-[#0078B6]">{{ auth()->user()->name }}</a> al
                área legal de
                @if (in_array($user['park_id'], [1, 2,3,4,5,6,7,8,9,10,11,12]))
                <span> Starpark </span>
                @else
                <span>JumpingLand</span>
                @endif
            </h1>
            <button @click="filter = !filter" :class="filter ? 'hidden' : 'visible' ">
                <i class="fa-solid fa-sliders fa-rotate-90"></i>
            </button>
            <div class="space-x-4" x-show="filter === true">
                <label for="date">Desde: </label>
                <input wire:model.live="start_date" class="rounded-xl" type="date" id="date" min="2026-01-01"
                    max="2030-01-01">
                <label for="end_date">Hasta: </label>
                <input wire:model.live="end_date" class="rounded-xl" type="date" id="end_date" min="2026-01-01"
                    max="2030-01-01">
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
        <div class="mt-4">
            <div
                class="bg-white shadowCard hover:shadow rounded-2xl overflow-hidden border border-gray-100 transition-all duration-300">
                <div class="p-8 flex flex-col items-center justify-center" style="min-height: 400px">
                    <div class="bg-blue-50 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fa-regular fa-file-lines text-blue-600 text-4xl"></i>
                    </div>

                    <h3 class="text-gray-500 text-xs font-black uppercase tracking-[0.2em] mb-2">
                        Consentimientos Totales
                    </h3>

                    <div class="flex items-baseline space-x-1">
                        <span class="text-6xl font-black text-gray-900 tracking-tighter">
                            {{ number_format($park_data['total_consents'] ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div wire:key="consent-{{ $start_date }}-{{ $end_date }}" wire:ignore
                class="bg-white shadowCard hover:shadow rounded-lg overflow-hidden">
                <div class="p-4">
                    <h3 class="text-sm font-bold text-gray-700 mb-2">Consentimientos por Atracción</h3>
                    <div class="w-full" style="min-height: 400px" id="chart_by_park"
                        x-init="$nextTick(() => safeInitCharts())"
                        data-series="{{ json_encode($park_data['chart_data']['series'] ?? []) }}"
                        data-labels="{{ json_encode($park_data['chart_data']['labels'] ?? []) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="mt-4 bg-white shadowCard hover:shadow rounded-2xl border border-gray-100 overflow-hidden transition-all duration-300">

        {{-- Encabezado --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-bold text-gray-700">Clientes</h3>
                <p class="text-xs text-gray-400">Listado de clientes registrados</p>
            </div>
            <div class="relative w-full sm:w-72">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar cliente..."
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border-gray-200 focus:border-[#0078B6] focus:ring-[#0078B6]">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Id</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Nombre</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Parque</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Celular</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Creado</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Actualizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users['data']['data'] as $client)
                    @php
                    $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                    $initials = collect(explode(' ', $fullName))->filter()->take(2)
                    ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
                    @endphp
                    <tr wire:key="client-{{ $client->id }}" class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-gray-400">
                            #{{ $client->id ?? '--' }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 shrink-0 rounded-full bg-blue-100 text-[#0078B6] flex items-center justify-center text-xs font-bold">
                                    {{ $initials ?: '?' }}
                                </div>
                                <span class="font-medium text-gray-900 whitespace-nowrap">
                                    {{ $fullName ?: '--' }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if ($client->parks_id && $client->park)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 whitespace-nowrap">
                                {{ $client->park['name'] }}
                            </span>
                            @else
                            <span class="text-gray-400">--</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $client->phone ?? '--' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->format('d/m/Y H:i') : '--' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ $client->updated_at ? \Carbon\Carbon::parse($client->updated_at)->diffForHumans() : '--' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="bg-gray-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-users-slash text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-base font-bold text-gray-800">No se encontraron clientes
                                </h3>
                                <p class="text-sm text-gray-500">Agrega nuevos clientes
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
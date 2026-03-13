<?php

use App\Http\Controllers\Operation\DashboardController;
use Livewire\Volt\Component;
use Carbon\Carbon;

new class extends Component {
    public $start_date;
    public $end_date;

    public function with()
    {
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');

        $this->end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
        $data = app(DashboardController::class)->getAllMetrics($this->start_date, $this->end_date);
        $park = app(DashboardController::class)->getMetricsByPark();
        return [
            'data' => $data,
            'park' => $park
        ];
    }
}; ?>

<div class="py-4">
    <div x-data="{filter:false}" class="space-x-4 mx-auto px-4">
        <div class="flex justify-between p-4 bg-white shadow sm:rounded-lg">
            <h1 class="text-xl font-semibold text-gray-900">
                Bienvenid@ <a href="{{ route('profile') }}" class="text-[#0078B6]">{{ auth()->user()->name }}</a> al
                área legal de StarPark
            </h1>
            <button @click="filter = !filter" :class="filter ? 'hidden' : 'visible' ">
                <i class="fa-solid fa-sliders fa-rotate-90"></i>
            </button>
            <div class="space-x-4" x-show="filter === true">
                <label for="date">Desde: </label>
                <input wire:model="start_date" class="rounded-xl" type="date" id="date" min="2026-01-01"
                    max="2030-01-01">
                <label for="end_date">Hasta: </label>
                <input wire:model="end_date" class="rounded-xl" type="date" id="end_date" min="2026-01-01"
                    max="2030-01-01">
            </div>
        </div>
    </div>

    <div class="w-full sm:px-2 lg:px-2">
        <div id="charts-container"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">

            <div class="mt-4">
                <div wire:ignore
                    class="bg-white shadowCard hover:shadow rounded-2xl overflow-hidden border border-gray-100 transition-all duration-300">
                    <div class="p-8 flex flex-col items-center justify-center" style="min-height: 400px">
                        <div class="bg-blue-50 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                            <i class="fas fa-file-signature text-blue-600 text-2xl"></i>
                        </div>

                        <h3 class="text-gray-500 text-xs font-black uppercase tracking-[0.2em] mb-2">
                            Consentimientos Totales
                        </h3>

                        <div class="flex items-baseline space-x-1">
                            <span class="text-6xl font-black text-gray-900 tracking-tighter">
                                {{ number_format($data['total_consents']) }}
                            </span>
                        </div>


                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div wire:ignore class="bg-white shadowCard hover:shadow rounded-lg overflow-hidden">
                    <div class="p-4">
                        <h3 class="text-sm font-bold text-gray-700 mb-2">Acompañantes Frecuentes </h3>
                        <div class="w-full" style="min-height: 400px" id="chart_radial"
                            data-series="{{ json_encode($data['count_parent']['series']) }}"
                            data-labels="{{ json_encode($data['count_parent']['labels'] ?? ['A', 'B', 'C']) }}"
                            data-total="{{ $data['total_consents'] }}">
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="w-full mt-4">
            <div wire:ignore class="w-full bg-white shadowCard hover:shadow rounded-lg">
                <div class="p-4">
                    <div class="w-full" style="min-height: 500px" id="chart2"
                        data-series="{{ json_encode($data['chart_data']['series']) }}"
                        data-categories="{{ json_encode($data['chart_data']['categories'] ?? ['A', 'B', 'C']) }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            @dump($park)
            <div wire:ignore
                class="bg-white shadowCard hover:shadow rounded-2xl overflow-hidden border border-gray-100 transition-all duration-300">
                <div class="p-8 flex flex-col items-center justify-center" style="min-height: 400px">
                    <div class="bg-blue-50 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-signature text-blue-600 text-2xl"></i>
                    </div>

                    <h3 class="text-gray-500 text-xs font-black uppercase tracking-[0.2em] mb-2">
                        Consentimientos Totales
                    </h3>

                    <div class="flex items-baseline space-x-1">
                        <span class="text-6xl font-black text-gray-900 tracking-tighter">
                            {{ number_format($data['total_consents']) }}
                        </span>
                    </div>


                </div>
            </div>
        </div>

    </div>
</div> @vite(['resources/js/chart.js'])
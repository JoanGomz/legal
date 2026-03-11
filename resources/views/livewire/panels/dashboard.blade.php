<?php

use Livewire\Volt\Component;

new class extends Component {
    public $start_date;
    public $end_date;
}; ?>

<div class="py-4">
    <div x-data="{filter:false}" class=" space-x-4 mx-auto px-4">
        <div class=" flex justify-between p-4 bg-white shadow sm:rounded-lg">
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
    <div class=" mx-auto sm:px-4 lg:px-4">

        <div id="charts-container"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

            <div class="row mt-4">
                <div class="col-md-5 mb-4">
                    <div class="bg-white p-5 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 ">
                        <div id="sparkline3"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadowCard hover:shadow overflow-hidden rounded-lg">
                <div class="p-4">
                    <div class="w-full flex justify-center" style="min-height: 300px" id="chart2">
                    </div>
                </div>
            </div>


            <div class="bg-white shadowCard hover:shadow overflow-hidden rounded-lg p-8 text-center">
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-4">

                </h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">

                </div>
                <p class="text-gray-400 text-sm">

                </p>
            </div>


            <div class="bg-white shadowCard hover:shadow overflow-hidden rounded-lg">
                <div class="p-4">
                    <div class="w-full flex justify-center" style="min-height: 300px" id="chart3">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@script
<script>
    const spark3 = {
        chart: {
            id: 'sparkline3',
            type: 'line',
            height: 100,
            sparkline: {
                enabled: true
            },
            group: 'sparklines'
        },
        series: [{
            name: 'red',
            data: [47, 45, 74, 32, 56, 31, 44, 33, 45, 19]
        }],
        stroke: {
            curve: 'smooth'
        },
        markers: {
            size: 0
        },
        tooltip: {
            fixed: {
                enabled: true,
                position: 'right',

            },
            x: {
                show: true
            }
        },
        colors: ['#020617'],
        title: {
            text: '577',
            style: {
                fontSize: '26px'
            }
        },
        xaxis: {
            crosshairs: {
                width: 1
            },
        }
    }
    new ApexCharts(document.querySelector("#sparkline3"), spark3).render();
</script>

@endscript
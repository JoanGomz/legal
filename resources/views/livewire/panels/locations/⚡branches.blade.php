<?php

use App\Http\Controllers\Operation\ParksController;
use App\Models\Base\Cities;
use Livewire\Component;

new class extends Component
{
    public function with()
    {

        $sedes = app(ParksController::class)->index();

        return [
            'sedes' => $sedes,

        ];
    }
};
?>
@component('livewire.panels.locations.locations-layout')
<div x-data="{
        sedeForm: false,
        titleModal: 'Creación de Sedes',
        textButton: 'Crear Sede',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Sedes' : 'Actualización de Sedes';
            this.method = type == 'create' ? 'create' : 'update';
            this.sedeForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4">

    <div class="flex justify-between items-center">
        <x-input-search mode="tableSearch" placeholder="Buscar Sede"></x-input-search>
        <button @click="prepareModal('create','Crear Sede')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-building mr-2"></i> Nueva Sede
        </button>
    </div>

    <div
        class="p-4 bg-white rounded-lg mb-2 w-full max-h-[600px] overflow-x-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4 py-4">
        @foreach( $sedes['data'] as $sede )
        <div class="div p-4 bg-gradient-to-r from-slate-800 to-slate-950 rounded-xl shadow-xl overflow-hidden relative">
            <div class="p-4 relative z-10">
                <span
                    class="absolute top-3 right-3 bg-black/30 backdrop-blur-sm px-3 py-1 text-xs font-mono rounded-full text-white/90 font-semibold">

                    {{ $sede->id }}</span>

                <div class="flex items-center mb-4">
                    <div class="bg-white/10 p-2 rounded-lg mr-3 backdrop-blur-sm">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-white"> {{$sede-> name }}</h2>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">

                    <div class="space-y-3 mb-5">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-200 mt-0.5 mr-2 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-blue-100">
                                <span class="font-semibold text-white">Horarios:</span><br>
                                Lunes a domingos: '8:00 AM - 6:00 PM'
                            </p>
                        </div>

                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-200 mt-0.5 mr-2 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-sm text-blue-100">
                                <span class="font-semibold text-white">Dirección:</span> {{ $sede->address }}<br>

                            </p>
                        </div>
                        <!-- Fechas -->
                        <div class="flex flex-wrap gap-2 mb-3 flex-col">
                            <div class="flex-1">
                                <span
                                    class="inline-block w-full px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-700/50 text-white backdrop-blur-sm border border-blue-500/30">
                                    <span class="font-semibold">Creado:</span>
                                    @if ($sede->create_at)
                                    {{ \Carbon\Carbon::parse($sede->create_at)->locale('es')->format('d/m/Y h:i A') }}
                                    @else
                                    <span>Desconocido</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-1">
                                <span
                                    class="inline-block w-full px-2.5 py-1.5 rounded-lg text-xs font-medium bg-indigo-700/50 text-white backdrop-blur-sm border border-indigo-500/30">
                                    <span class="font-semibold">Actualizado:</span>
                                    @if ($sede->update_at)
                                    {{ \Carbon\Carbon::parse($sede->update_at)->locale('es')->format('d/m/Y h:i A') }}
                                    {{ $sede->user_last_update }}
                                    @else
                                    <span>No actualizado</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-1">
                                <span
                                    class="inline-block w-full px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-700/50 text-white backdrop-blur-sm border border-blue-500/30">
                                    <span class="font-semibold">Autor:</span>
                                    @if ($sede->user_creator)
                                    {{ $sede->user_creator }}
                                    @else
                                    <span>Desconocido</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>


                    <div class="flex w-full gap-2 mt-auto">

                        <button
                            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-medium px-4 py-2 rounded-lg flex-1 shadow-sm transition-colors duration-200 border border-white/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </button>



                        <button @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                    detail: {
                                    }
                                }))"
                            class="bg-red-500/70 hover:bg-red-600/90 backdrop-blur-sm text-white font-medium px-4 py-2 rounded-lg flex-1 shadow-sm transition-colors duration-200 border border-red-500/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Eliminar
                        </button>

                    </div>

                </div>




                <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-400/20 rounded-full blur-xl"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl"></div>
                @endforeach
            </div>
        </div>
        <livewire:panels.locations.components.branches-modal />
    </div>
    <x-buttons-pagination></x-buttons-pagination>
    @endcomponent
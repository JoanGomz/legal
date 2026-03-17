<?php

use App\Http\Controllers\Operation\ParksController;
use App\Models\Base\Cities;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{

    use traitCruds;
    public function delete($id)
    {
        try {
            $this->response = app(ParksController::class)->destroy($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar la sede");
        }
    }
    #[On('refresh-user-list')]
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

    <div class="flex justify-between items-center px-4">
        <x-input-search mode="tableSearch" id="searchInput" placeholder="Buscar Sede"></x-input-search>
        @can('sede.create')
        <button @click="prepareModal('create','Crear Sede')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-building mr-2"></i> Nueva Sede
        </button>
        @endcan
    </div>
    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4 bg-neutral-primary-soft shadow-xs rounded-base relative">
        @foreach( $sedes['data'] as $sede )
        <div
            class="div p-4 bg-gradient-to-r from-slate-800 to-slate-950 rounded-xl shadow-xl overflow-hidden relative flex flex-col min-h-[400px]">
            <div class="p-4 relative z-10 flex-1 flex flex-col">
                <span
                    class="absolute top-3 right-3 bg-black/30 backdrop-blur-sm px-3 py-1 text-xs font-mono rounded-full text-white/90 font-semibold">
                    {{ $sede->id }}
                </span>

                <div class="flex items-center mb-4">
                    <div class="bg-white/10 p-2 rounded-lg mr-3 backdrop-blur-sm">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">{{ $sede->name }}</h2>
                </div>
                <div class="flex items-center mb-4">
                    @if(strtolower($sede->type) == 'parque')
                    <i class="fa-solid fa-star text-blue-200 mt-1 mr-3"></i>
                    @elseif(strtolower($sede->type) == 'centro_comercial')
                    <i class="fa-solid fa-shop  text-blue-200 mt-1 mr-3"></i>
                    @else
                    <i class="fa-solid fa-building  text-blue-200 mt-1 mr-3"></i>
                    @endif
                    <h2 class="text-lg font-bold tracking-tight text-white">{{ $sede->type }}</h2>
                </div>
                <div class="flex items-center mb-4">
                    <i class="fa-solid fa-city text-blue-200 mt-1 mr-3"></i>
                    <h2 class="text-lg font-bold tracking-tight text-white">{{ $sede->city->name }}</h2>
                </div>
                <div class="space-y-3 mb-5 flex-1">

                    <div class="flex items-start">
                        <i class="fa-solid fa-location-dot text-blue-200 mt-1 mr-3"></i>
                        <p class="text-sm text-blue-100">
                            <span class="font-semibold text-white">Dirección:</span><br> {{ $sede->address }}
                        </p>
                    </div>
                    <div class="flex w-full">
                        <div class="flex w-full gap-2 mt-auto">
                            <div class="flex-1">
                                <span
                                    class="block px-2.5 py-1.5 rounded-lg text-sm bg-blue-700/50 text-white border border-blue-500/30">
                                    <strong>Creado:</strong><br>
                                    {{ $sede->created_at ? \Carbon\Carbon::parse($sede->created_at)->format('d/m/Y h:i A') : 'N/A' }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <span
                                    class="block px-2.5 py-1.5 rounded-lg text-sm bg-indigo-700/50 text-white border border-indigo-500/30">
                                    <strong>Actualizado:</strong><br>
                                    {{ $sede->updated_at ? \Carbon\Carbon::parse($sede->updated_at)->format('d/m/Y h:i A') : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @can('park')
                <div class="flex w-full gap-2 h-auto mt-auto">
                    @can('edit.park')
                    <button wire:click="$dispatchTo('panels.locations.components.branches-modal', 'setEditingSede', @js([
                        'id'      => $sede->id,
                        'name'    => $sede->name,
                        'address' => $sede->address,
                        'city'    => $sede->city_id, 
                        'type'    => $sede->type
                            ]))"
                        class="bg-white/10  hover:bg-white/20 text-white text-base font-bold px-4 py-2 rounded-lg flex-1 border border-white/10 flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </button>
                    @endcan
                    @can('delete.park')
                    <button
                        @click="window.dispatchEvent(new CustomEvent('show-delete-modal', { detail: { id: {{ $sede->id }}, name: '{{ $sede->name }}' } }))"
                        class="bg-red-500/70 hover:bg-red-600/90 text-white text-base font-bold px-4 py-2 rounded-lg flex-1 border border-red-500/30 flex items-center justify-center">
                        <i class="fa-solid fa-trash-can mr-2"></i> Eliminar
                    </button>
                    @endcan
                </div>
                @endcan
            </div>

            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-400/20 rounded-full blur-xl"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl"></div>
        </div> @endforeach
    </div>
    <livewire:panels.locations.components.branches-modal />
    <x-buttons-pagination></x-buttons-pagination>
</div> @endcomponent
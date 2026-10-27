<?php

use App\Http\Controllers\Base\BrandsController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use traitCruds;
    public function delete($id)
    {
        try {
            $this->response = app(BrandsController::class)->delete($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar la brand");
        }
    }
    #[On('refresh-user-list')]
    public function with()
    {
        $brand = app(BrandsController::class)->indexPaginated($this->page, $this->perPage, $this->search);

        return [
            'brand' => $brand
        ];
    }
};
?>
@component('livewire.panels.configuration.config-layout')
<div x-data="{
        brandForm: false,
        titleModal: 'Creación de Brand',
        textButton: 'Crear Brand',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Brand' : 'Actualización de Brand';
            this.method = type == 'create' ? 'create' : 'update';
            this.brandForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4 m-4">

    <div class="flex justify-between items-center px-4">
        <x-input-search mode="tableSearch" id="searchInput" placeholder="Buscar Brand"></x-input-search>

        <button @click="prepareModal('create','Crear Brand')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-plus mr-2"></i> Nueva Brand
        </button>
    </div>
    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4 bg-neutral-primary-soft shadow-xs rounded-base relative">
        @dump($brand['data'])
        @foreach( $brand['data'] as $sede )
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
                    <h2 class="text-2xl font-bold tracking-tight text-white">{{ $sede->description }}</h2>
                </div>
                <div class="space-y-3 mb-5 flex-1">
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
                <div class="flex w-full gap-2 h-auto mt-auto">
                    <button wire:click="$dispatchTo('panels.configuration.component.brand-modal', 'setEditingBrand', @js([
                        'id'      => $sede->id,
                        'name'    => $sede->name,
                        'description' => $sede->description
                            ]))"
                        class="bg-white/10  hover:bg-white/20 text-white text-base font-bold px-4 py-2 rounded-lg flex-1 border border-white/10 flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </button>

                    <button
                        @click="window.dispatchEvent(new CustomEvent('show-delete-modal', { detail: { id: {{ $sede->id }}, name: '{{ $sede->name }}' } }))"
                        class="bg-red-500/70 hover:bg-red-600/90 text-white text-base font-bold px-4 py-2 rounded-lg flex-1 border border-red-500/30 flex items-center justify-center">
                        <i class="fa-solid fa-trash-can mr-2"></i> Eliminar
                    </button>
                </div>
            </div>

            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-400/20 rounded-full blur-xl"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl"></div>
        </div>
        @endforeach
    </div>
    <livewire:panels.configuration.component.brand-modal />
    <x-buttons-pagination></x-buttons-pagination>
</div>
@endcomponent
<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use traitCruds;

    public function delete($id)
    {
        try {
            $this->response = app(AtraccionArcadeController::class)->destroy($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar la atracción");
        }
    }
    #[On('refresh-atracction-list')]
    public function with()
    {

        $atraccion = app(AtraccionArcadeController::class)->getPaginated($this->page, $this->perPage, $this->search);
        return [
            'atraccion' => $atraccion
        ];
    }
};
?>
@component('livewire.panels.locations.locations-layout')
<div x-data="{
        atracctionForm: false,
        titleModal: 'Creación de atracciones',
        textButton: 'Crear Atracción',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Atracciones' : 'Actualización de Atracciones';
            this.method = type == 'create' ? 'create' : 'update';
            this.atraccionForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4">

    <div class="flex justify-between items-center px-4">
        <x-input-search mode="tableSearch" placeholder="Buscar Sede"></x-input-search>
        @can('create.atracction')
        <button @click="atracctionForm = true; prepareModal('create','Crear Atracción')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-plus mr-2"></i> Nueva Atraccion
        </button>
        @endcan
    </div>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">Id</th>
                    <th scope="col" class="px-6 py-3 font-medium">Nombre</th>
                    <th scope="col" class="px-6 py-3 font-medium">Estado</th>
                    <th scope="col" class="px-6 py-3 font-medium">Información</th>
                    <th scope="col" class="px-6 py-3 font-medium">Auditorias</th>
                    <th scope="col" class="px-6 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($atraccion['data'] as $atr)
                <tr class="bg-neutral-primary-soft border-b  border-default">
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                        {{ $atr->id }}
                    </th>
                    <td class="px-2 py-4">
                        <div class="relative group">
                            <!-- Vista limpia - solo lo esencial -->
                            <div class="cursor-pointer">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $atr->nombre ?? 'Sin nombre' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fa-solid fa-building mr-1"></i>
                                    {{ $atr->park->name ?? 'Sin Parque' }}
                                </div>
                            </div>
                            <!-- Tooltip con información completa -->
                            <div
                                class="absolute left-0 top-full mt-2 w-72 bg-slate-950 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                <h4 class="font-semibold text-yellow-400 mb-2">Detalles de la Máquina</h4>
                                <div class="space-y-1">
                                    @if ($atr->descripcion)
                                    <div><span class="font-semibold">Descripción:</span>
                                        {{ Str::limit($atr->descripcion, 100) }}
                                    </div>
                                    @endif
                                    <div><span class="font-semibold">Parque:</span>
                                        {{ $atr->park->name ?? 'N/A' }}
                                    </div>
                                    <div><span class="font-semibold">Ubicación:</span>
                                        {{ $atr->ubicacion ?? 'N/A' }}
                                    </div>
                                    <div><span class="font-semibold">Tipo:</span>
                                        {{ $atr->tipo ?? 'N/A' }}
                                    </div>
                                </div>
                                <!-- Flecha del tooltip -->
                                <div class="absolute -top-1 left-4 w-2 h-2 bg-gray-800 transform rotate-45">
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $atr->estado }}
                    </td>
                    <td class="px-2 py-4 items-center">
                        <div class="relative group">

                            <div class="cursor-pointer flex items-center relative" style="right: -33px;">
                                <i
                                    class="fa-solid fa-info-circle text-gray-400 ml-2 group-hover:text-blue-500 transition-colors"></i>
                            </div>
                            <!-- Tooltip con información completa -->
                            <div
                                class="absolute left-0 top-full mt-2 w-72 bg-white text-black text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                <h4 class="font-semibold text-center text-black mb-2">Detalles de la Máquina</h4>
                                <div class="space-y-1">
                                    @if ($atr->numero_serie)
                                    <div><span class="font-semibold">Número de Serie:</span>
                                        {{ $atr->numero_serie ?? 'Sin numero de serie'}}
                                    </div>
                                    @endif
                                    @if ($atr->promedio_consumo)
                                    <div><span class="font-semibold">Consumo:</span>
                                        {{ Str::limit($atr->promedio_consumo) }} Kw
                                    </div>
                                    @endif
                                    <div><span class="font-semibold">MAC:</span>
                                        {{ $atr->device_mac ?? 'N/A' }}
                                    </div>
                                    @if ($atr->capacidad)
                                    <div><span class="font-semibold">Capacidad:</span>
                                        {{ $atr->capacidad ?? 'Sin camapcidad'}} Personas
                                    </div>
                                    @endif
                                    <div><span class="font-semibold">Tiempo de juego:</span>
                                        {{ $atr->tiempo_juego ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-4">
                        <div class="relative group">
                            <!-- Vista limpia - solo fecha -->
                            <div class="cursor-pointer">
                                @if ($atr->created_at)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($atr->created_at)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($atr->created_at)->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-sm text-gray-400">Sin fecha</div>
                                @endif
                                <i
                                    class="fa-solid fa-history text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                            </div>

                            <!-- Tooltip con información completa de auditoría -->
                            <div
                                class="absolute right-0 top-full mt-2 w-64 bg-gray-800 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                <h4 class="font-semibold text-center text-yellow-400 mb-2">Información de Auditoría
                                </h4>
                                <div class="space-y-1">
                                    @if (isset($atr->userCreator->name))
                                    <div><span class="font-semibold">Creado por:</span>
                                        {{ $atr->userCreator->name }}
                                    </div>
                                    @endif
                                    @if ($atr->created_at)
                                    <div><span class="font-semibold">Fecha de creación:</span>
                                        {{ \Carbon\Carbon::parse($atr->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                    <div><span class="font-semibold">Hace:</span>
                                        {{ \Carbon\Carbon::parse($atr->created_at)->diffForHumans() }}
                                    </div>
                                    @endif
                                    @if ($atr->updated_at && $atr->updated_at != $atr->created_at)
                                    <div class="pt-2 border-t border-gray-600">
                                        <div>
                                            <span class="font-semibold">Última actualización:</span>
                                        </div>
                                        <div>
                                            {{ \Carbon\Carbon::parse($atr->updated_at)->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-gray-300">
                                            {{ \Carbon\Carbon::parse($atr->updated_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-4 flex gap-2 relative" style="right:-13px;">
                        @can('edit.atracction')
                        <button aria-label="Editar atracción" type="button" wire:loading.attr="disabled"
                            @click="atracctionForm = true; prepareModal('update', 'Actualizar Atracciones')"
                            wire:loading.class="opacity-50 cursor-not-allowed" wire:click="$dispatchTo('panels.locations.components.atracctions-modal','setEditingAtracction', 
                            {data: @js([
                        'id'          => $atr->id,
                        'name'        => $atr->nombre,
                        'descripcion' => $atr->descripcion,
                        'type'        => $atr->tipo, 
                        'capacity'    => $atr->capacidad,
                        'consume'     => $atr->promedio_consumo,
                        'time'        => $atr->tiempo_juego,
                        'serial'      => $atr->numero_serie,
                        'mac'         => $atr->device_mac,
                        'parque'      => $atr->id_park,
                        'ubication'   => $atr->ubicacion,
                        'state'       => $atr->estado
                    ]) 
                    })">
                            <i class="fa-solid fa-square-pen fa-xl text-blue-500"></i>
                        </button>
                        @endcan
                        @can('delete.atracction')
                        <button type="button" aria-label="Eliminar Atraccion" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed" @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                                    detail: {
                                                        id: {{ $atr['id'] }},
                                                        name: '{{ $atr['name'] }}'
                                                    }
                                                }))">
                            <i class="fa-solid fa-trash fa-xl text-red-500"></i>
                        </button>
                        @endcan
                    </td>
                </tr>
                @empty
                <div>
                    <h3 class="text-lg font-bold text-gray-800">No se encontraron atracciones</h3>
                    <p class="text-sm text-gray-500">Agrega nuevas atracciones para comenzar a gestionar el acceso</p>
                </div>
                @endforelse
            </tbody>
        </table>
    </div>

    <livewire:panels.locations.components.atracctions-modal />
    <x-buttons-pagination></x-buttons-pagination>
</div>
@endcomponent
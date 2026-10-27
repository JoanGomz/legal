<?php

use App\Http\Controllers\Base\BrandsController;
use App\Http\Controllers\Operation\ClientController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use traitCruds;
    public $brand;
    public $brandSelect;
    public $park;

    public function getBrand($id)
    {
        if (auth()->user()->hasRole('SuperAdmin')) {

            $this->brandSelect = ($this->brandSelect === $id) ? null : $id;
            $this->brand = $this->brandSelect;
            if ($this->brand == '2') {
                $this->park = 13;
            } else {
                $this->park = 12;
            }
            $this->dispatch('hide-loading');
        }
    }
    #[On('refresh-client-list')]
    public function with()
    {
        $users = app(ClientController::class)->indexPaginated($this->page, $this->perPage, $this->search, (int)$this->brand, (int)$this->park);
        $brands = app(BrandsController::class)->indexPaginated($this->page, $this->perPage, $this->search);
        return [
            'users' => $users,
            'brands' => $brands
        ];
    }
    public function delete($id)
    {
        try {
            $this->response = app(ClientController::class)->delete($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar el usuario");
        }
    }
};
?>
@component('livewire.panels.clients.clients-layout')
<div x-data="{
        clientForm: false,
        titleModal: 'Creación de Clientes',
        textButton: 'Crear Cliente',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Cliente' : 'Actualización de Cliente';
            this.method = type == 'create' ? 'create' : 'update';
            this.clientForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4 m-4">
    <div class="flex justify-between items-center">
        <x-input-search mode="tableSearch" placeholder="Buscar Clientes"></x-input-search>

        <button @click="prepareModal('create','Crear Cliente')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Cliente
        </button>

    </div>
    @if(Auth()->user()->hasRole('SuperAdmin'))
    <div
        class="bg-white shadow-sm rounded-2xl border border-slate-200/80 overflow-hidden mb-6 transition-colors duration-300">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-950 shadow-sm shadow-orange-500/20">
                    <i class="fa-solid fa-tag text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 tracking-tight">
                        Brands Activas
                    </h3>
                    <p class="text-sm text-slate-500">
                        Selecciona una Brand Para ver su Información.
                    </p>
                </div>
            </div>

        </div>

        <main class="relative grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6 overflow-hidden">

            @forelse ($brands['data'] ?? [] as $item)
            <div wire:click="getBrand({{ $item['id'] }})" @click="$dispatch('show-loading', { message: 'Cargando...' })"
                class="group relative overflow-hidden rounded-xl p-5 cursor-pointer backdrop-blur-xl backdrop-saturate-150 border border-white/70 shadow-lg shadow-slate-200/50 
                   hover:border-white hover:shadow-xl hover:shadow-[#048AC1]/15 hover:-translate-y-0.5 transition-all duration-300">

                {{-- Top accent line --}}
                <div
                    class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#01305A] via-[#024F86] to-[#048AC1] opacity-50 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                {{-- Glow decorativo de fondo --}}
                <div
                    class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-gradient-to-br from-[#01305A]/40 to-[#024F86]/40 blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500">
                </div>

                {{-- Brillo diagonal sutil --}}
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-white/25 via-transparent to-transparent opacity-70 pointer-events-none">
                </div>

                <div class="relative flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/75  backdrop-blur-sm border border-white/6 text-[#048AC1] group-hover:bg-gradient-to-br group-hover:from-[#01305A] group-hover:via-[#024F86] group-hover:to-[#048AC1]
                                group-hover:text-white group-hover:border-transparent transition-all duration-300 mb-3 shadow-sm">
                            <i class="fa-solid fa-building text-sm"></i>
                        </div>

                        <h4
                            class="text-sm font-semibold text-slate-800 truncate group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-[#01305A] group-hover:to-[#024F86] transition-all">
                            {{ $item['name'] }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                            Usuarios asociados a {{ $item['name'] }}.
                        </p>
                    </div>

                    <button type="button" wire:click.stop="getBrand({{ $item['id'] }})" class="relative shrink-0 w-8 h-8 flex items-center justify-center rounded-lg
                           bg-white/65  backdrop-blur-sm  border border-white/55    text-slate-500 
                           group-hover:bg-gradient-to-br group-hover:from-[#01305A] group-hover:to-[#024F86]
                           group-hover:text-white group-hover:border-transparent
                           transition-colors duration-300 shadow-sm">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full flex flex-col items-center justify-center py-14 text-center">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                    <i class="fa-solid fa-building-circle-xmark text-lg"></i>
                </div>
                <p class="text-sm font-medium text-slate-600">No se encontraron centros comerciales
                </p>
                <p class="text-xs text-slate-400 mt-1">Intenta más tarde o contacta al
                    administrador.</p>
            </div>
            @endforelse
        </main>
    </div>
    @endif
    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Id
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Nombre
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Parque
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Celular
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Creado
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Actualizado
                    </th>

                    <th scope="col" class="px-6 py-3 font-medium">
                        Acciones
                    </th>


                </tr>
            </thead>
            <tbody>
                @forelse ($users['data']['data'] as $user)
                <tr class="bg-neutral-primary-soft border-b  border-default">
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                        {{ $user->id ?? '--'}}
                    </th>
                    <td class="px-6 py-4">
                        {{ $user->first_name ?? '--'}} {{ $user->last_name}}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->parks_id ? $user->park['name'] : '--'  }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->phone ?? '--'}}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->created_at ?? '--'}}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->updated_at ?? '--'}}
                    </td>

                    <td class="px-2 py-4 flex gap-2 justify-center md:relative" style="left: -2.5rem;">

                        <button aria-label="Editar permiso" type="button" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:click="$dispatchTo('panels.clients.components.modal-client', 'setEditingClient', { id: @js($user) })">
                            <i class="fa-solid fa-square-pen fa-xl text-blue-500"></i>
                        </button>

                        <button type="button" aria-label="Eliminar Permiso" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed" @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                                    detail: {
                                                        id: {{ $user['id'] }},
                                                        name: '{{ $user['name'] }}'
                                                    }
                                                }))">
                            <i class="fa-solid fa-trash fa-xl text-red-500"></i>
                        </button>


                    </td>

                </tr>
                @empty
                <div>
                    <h3 class="text-lg font-bold text-gray-800">No se encontraron usuarios</h3>
                    <p class="text-sm text-gray-500">Agrega nuevos usuarios para comenzar a gestionar el acceso</p>
                </div>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-buttons-pagination :data="$users"></x-buttons-pagination>
    <livewire:panels.clients.components.modal-client />
</div>
@endcomponent
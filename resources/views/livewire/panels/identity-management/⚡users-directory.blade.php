<?php

use Livewire\Component;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Base\BrandsController;
use App\Http\Controllers\Operation\ParksController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;

new class extends Component {
    use traitCruds;
    public $brandSelect;
    public $park;

    public function mount()
    {
        if (auth()->user()->hasRole('SuperAdmin')) {
            if ($this->brand == 2) {
                $this->park = 13;
            }
        }
    }
    public function selectPark(?int $id): void
    {
        $this->park = $id;
        $this->resetPage();
    }
    public function refreshData()
    {
        $this->with();
    }
    public function delete($id)
    {
        try {
            $this->response = app(UserController::class)->destroy($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar el usuario");
        }
    }
    #[On('refresh-user-list')]
    public function with()
    {
        $this->mount();
        $users = app(UserController::class)->indexPaginated($this->page, $this->perPage, $this->search, $this->brand, $this->park);
        $parks = app(ParksController::class)->index($this->brand);
        $brands = app(BrandsController::class)->indexPaginated($this->page, $this->perPage, $this->search);
        return [
            'users' => $users,
            'brands' => $brands,
            'parks' => $parks
        ];
    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
<div x-data="{
        userForm: false,
        titleModal: 'Creación de Usuarios',
        textButton: 'Crear Usuario',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Usuario' : 'Actualización de Usuario';
            this.method = type == 'create' ? 'create' : 'update';
            this.userForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4">
    <div class="flex justify-between items-center">
        <x-input-search mode="tableSearch" placeholder="Buscar usuarios"></x-input-search>
        @if (auth()->user()->can('User.create') || auth()->user()->park_id == 13)
        <button type="button" @click="prepareModal('create', 'Crear Usuario')"
            class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
            <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Usuario
        </button>
        @endif
    </div>
    @if ($brand && auth()->user()->hasRole('SuperAdmin'))
    <div class="bg-white rounded-2xl border border-slate-200 mb-6">
        <div role="group" aria-label="Filtrar por parque" class="flex flex-wrap items-center gap-2 px-6 py-5">
            <span class="text-sm font-semibold text-slate-500 mr-1">Parque</span>

            @forelse ($parks['data'] ?? [] as $item)
            <button type="button" wire:key="park-chip-{{ $item['id'] }}" wire:click="selectPark({{ $item['id'] }})"
                @class([ 'h-11 px-4 rounded-full border text-sm font-medium whitespace-nowrap transition-colors'
                , 'bg-[#024F86] border-[#024F86] text-white'=> $park == $item['id'],
                'bg-white border-slate-300 text-slate-800 hover:border-slate-400' => $park != $item['id'],
                ])>
                {{ $item['name'] }}
            </button>
            @empty
            <span class="text-sm text-slate-500">Esta marca aún no tiene parques registrados.</span>
            @endforelse

            <i wire:loading wire:target="selectPark" class="fa-solid fa-spinner fa-spin text-slate-400 ml-2"></i>
        </div>
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
                        Correo
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Creado
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Actualizado
                    </th>
                    @if(auth()->user()->can('user') || auth()->user()->park_id == 13)
                    <th scope="col" class="px-6 py-3 font-medium">
                        Acciones
                    </th>
                    @endif

                </tr>
            </thead>
            <tbody>
                @forelse ($users['data'] as $user)
                <tr class="bg-neutral-primary-soft border-b  border-default">
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                        {{ $user->id }}
                    </th>
                    <td class="px-6 py-4">
                        {{ $user->name }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->park_id ? $user->park->name : '--'  }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->created_at }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->updated_at }}
                    </td>

                    <td class="px-2 py-4 flex gap-2 justify-center md:relative" style="left: -2.5rem;">
                        @if(auth()->user()->can('User.update') || auth()->user()->park_id == 13)
                        <button aria-label="Editar permiso" type="button" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:click="$dispatchTo('panels.identity-management.components.user-modal', 'setEditingUser', { id: {{ $user['id'] }} })">
                            <i class="fa-solid fa-square-pen fa-xl text-blue-500"></i>
                        </button>
                        @endif
                        @if(auth()->user()->can('User.delete') || auth()->user()->park_id == 13 )
                        <button type="button" aria-label="Eliminar Permiso" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed" @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                                    detail: {
                                                        id: {{ $user['id'] }},
                                                        name: '{{ $user['name'] }}'
                                                    }
                                                }))">
                            <i class="fa-solid fa-trash fa-xl text-red-500"></i>
                        </button>
                        @endif

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
    <livewire:panels.identity-management.components.user-modal />
</div>
@endcomponent
<?php

use Livewire\Component;
use App\Traits\traitCruds;
use App\Http\Controllers\Admin\PermissionController;
use Livewire\Attributes\On;
new class extends Component {
    use traitCruds;
    public function refreshData()
    {
        $this->with();
    }
    #[On('refresh-user-list')]
    public function with()
    {
        $permissions = app(PermissionController::class)->indexPaginated($this->page, $this->perPage, $this->search);
        return [
            'permissions' => $permissions,
        ];
    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
    <div x-data="{
        permissionForm: false,
        titleModal: 'Creación de Permisos',
        textButton: 'Crear Permiso',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Permisos' : 'Actualización de Permisos';
            this.method = type == 'create' ? 'create' : 'update';
            this.permissionForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Buscar Permiso"
                class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
            <button @click="prepareModal('create','Crear Permiso')"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-unlock mr-2"></i></i> Nuevo Permiso
            </button>
        </div>
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
                    @forelse ($permissions['data'] as $permission)
                        <tr class="bg-neutral-primary-soft border-b  border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                {{ $permission->id }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $permission->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $permission->created_at }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $permission->updated_at }}
                            </td>
                            <td class="px-2 py-4 flex gap-2 justify-center">
                                <button aria-label="Editar permiso" type="button" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:click="$dispatchTo('panels.identity-management.components.permission-modal', 'setEditingPermission', { id: {{ $permission['id'] }}, name: '{{ $permission['name'] }}' })">
                                    <i class="fa-solid fa-square-pen fa-xl text-blue-500"></i>
                                </button>
                                <button type="button" aria-label="Eliminar Permiso" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                                    detail: {
                                                        id: {{ $permission['id'] }},
                                                        name: '{{ $permission['name'] }}'
                                                    }
                                                }))">
                                    <i class="fa-solid fa-trash fa-xl text-red-500"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-2 py-4 text-center">No hay permisos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-buttons-pagination :data="$permissions"></x-buttons-pagination>
        <livewire:panels.identity-management.components.permission-modal />
    </div>
@endcomponent

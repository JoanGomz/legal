<?php

use Livewire\Component;
use app\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Admin\UserController;
use App\Traits\traitCruds;

new class extends Component {
    use traitCruds;
    public function delete($id){
        try {
            $response = app(UserController::class)->delete($id);
            $this->endPetition();
        } catch (\Throwable $th) {
            $this->handleException($th, "Ocurrio un error al intener borrar el usuario");
        }
    }
    public function with()
    {
        $users = app(UserController::class)->indexPaginated($this->page, $this->perPage, $this->search);
        return [
            'users' => $users,
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
            <button @click="prepareModal('create','Crear Usuario')"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Usuario
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
                            Correo
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
                    @forelse ($users['data'] as $user)
                        <tr class="bg-neutral-primary-soft border-b  border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                {{ $user->id }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $user->name }}
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
                            <td class="px-2 py-4 flex gap-2 justify-center">
                                <button aria-label="Editar permiso" type="button" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:click="$dispatchTo('panels.identity-management.components.user-modal', 'setEditingUser', { id: {{ $user['id'] }} })">
                                    <i class="fa-solid fa-square-pen fa-xl text-blue-500"></i>
                                </button>
                                <button type="button" aria-label="Eliminar Permiso" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
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
        <livewire:panels.identity-management.components.user-modal />
    </div>
@endcomponent

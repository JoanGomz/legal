<?php

use Livewire\Component;
use app\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Admin\UserController;
use App\Traits\traitCruds;

new class extends Component {
    use traitCruds;
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
        prepareModal(type) {
            this.titleModal = type == 'create' ? 'Creación de Usuarios' : 'Actualización de Usuarios';
            this.method = type == 'create' ? 'create' : 'update';
            this.userForm = true;
            this.textButton = 'Crear Usuario'
        },
    
    }" class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Buscar usuario"
                class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
            <button @click="prepareModal('create')"
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
                            <td class="px-6 py-4">
                                <a href="#" class="font-medium text-fg-brand hover:underline">Edit</a>
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

<?php

use Livewire\Component;
use App\Traits\traitCruds;
use App\Http\Controllers\Admin\RoleController;
use Livewire\Attributes\On;
new class extends Component {
    use traitCruds;
    #[On('refresh-user-list')]
    public function with()
    {
        $roles = app(RoleController::class)->index();
        return [
            'roles' => $roles,
        ];
    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
    <div x-data="{
        roleForm: false,
        titleModal: 'Creación de Roles',
        textButton: 'Crear Rol',
        method: 'create',
        init() {
            window.prepareModal = (type, text) => this.prepareModal(type, text);
        },
        prepareModal(type, textButton) {
            this.titleModal = type == 'create' ? 'Creación de Rol' : 'Actualización de Rol';
            this.method = type == 'create' ? 'create' : 'update';
            this.roleForm = true;
            this.textButton = textButton;
        },
    }" class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <x-input-search mode="cardSearch" placeholder="Buscar roles"></x-input-search>
            <button @click="prepareModal('create','Crear Rol')"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-unlock mr-2"></i></i> Nuevo Rol
            </button>
        </div>
        <div
            class="div grid grid-cols-3 gap-2 relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base ">
            @forelse($roles['data']['roles'] as $role)
                <div
                    class="div gap-2 relative bg-slate-600 text-white p-6 rounded-2xl shadow-lg">
                    <!-- Badge de ID -->
                    <span
                        class="absolute top-3 right-3 bg-black/20 px-2 py-1 text-xs font-mono rounded-md text-white/70">#{{ $role['id'] }}</span>

                    <!-- Encabezado -->
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold tracking-tight">{{ $role['name'] }}</h2>
                        <p class="text-sm font-medium text-blue-100 mt-1">{{ $role['guard_name'] }}</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-blue-100">Descripción:
                            {{ $role['description'] ?? 'Sin descripción' }}</p>
                    </div>
                    <!-- Permisos -->
                    <div class="mb-4">
                        <h3 class="text-sm uppercase tracking-wider text-blue-200 font-semibold mb-2">Permisos
                        </h3>
                        <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto pr-1">
                            @forelse ($role['permissions'] as $item)
                                <span class="bg-white/20 text-xs px-2 py-1 rounded-md backdrop-blur-sm">
                                    {{ $item['name'] }}
                                </span>
                            @empty
                                <span class="text-xs italic text-blue-200">Sin permisos asignados</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Metadatos -->
                    <div class="border-t border-white/10 pt-3 mb-4 text-xs text-blue-100 grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-blue-200">Creado:</span><br>
                            {{ \Carbon\Carbon::parse($role['created_at'])->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <span class="text-blue-200">Actualizado:</span><br>
                            {{ \Carbon\Carbon::parse($role['updated_at'])->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <!-- Acciones -->

                    <div class="flex gap-2 mt-auto">

                        <button
                        wire:click="$dispatchTo('panels.identity-management.components.role-modal', 'setEditingRole', {id: {{ $role['id'] }} })"
                            class="bg-white text-black font-medium px-4 py-2 rounded-lg flex-1 shadow-sm hover:bg-blue-50 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </button>

                        <button
                            @click="window.dispatchEvent(new CustomEvent('show-delete-modal', {
                                            detail: {
                                                id: {{ $role['id'] }},
                                                name: '{{ $role['name'] }}'
                                            }
                                        }))"
                            class="bg-red-500 text-white font-medium px-4 py-2 rounded-lg flex-1 shadow-sm hover:bg-red-600 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Eliminar
                        </button>
                    </div>

                    <!-- Efecto de gradiente para dar profundidad -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                </div>
            @empty
                No hay permisos disponibles
            @endforelse
        </div>
        <livewire:panels.identity-management.components.role-modal />
    </div>
@endcomponent

<?php

use Livewire\Component;
use App\Traits\traitCruds;
use App\Http\Controllers\Admin\RoleController;
new class extends Component {
    use traitCruds;
    public function with()
    {
        $roles = app(RoleController::class)->index();
        return [
            'roles' => $roles
        ];
    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
    <div x-data="{
        userForm: false,
        titleModal: 'Creación de Roles',
        textButton: 'Crear Rol',
        method: 'create',
        prepareModal(type,textButton) {
            this.titleModal = type == 'create' ? 'Creación de Roles' : 'Actualización de Roles';
            this.method = type == 'create' ? 'create' : 'update';
            this.userForm = true;
            this.textButton = textButton
        },
    
    }" class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Buscar Rol"
                class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
            <button @click="prepareModal('create','Crear Rol')"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Rol
            </button>
        </div>
        <div class="grid grid-cols-3 relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
            
        </div>
        <livewire:panels.identity-management.components.role-modal />
    </div>
@endcomponent

<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

@component('livewire.panels.identity-management.accesos-layout')
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-700">Directorio de Permisos</h3>
            <button class="bg-brand-purple text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Permiso
            </button>
        </div>

        <div class="border rounded-lg p-10 text-center text-gray-400 border-dashed">
            Tabla de permisos en desarrollo...
        </div>
    </div>
@endcomponent

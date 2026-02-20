<?php

use Livewire\Component;
use app\Models\User;
new class extends Component {
    public function with()
    {
        $this->dispatch('saved');
        return [
            'users' => User::select('id', 'name', 'email', 'created_at', 'updated_at')->toBase()->paginate(10),
        ];

    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
    <div class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <input type="text" placeholder="Buscar usuario"
                class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
            <button
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
                            Auditoría
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
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
                                <div class="relative group">
                                    <!-- Vista limpia - solo lo esencial -->
                                    <div class="cursor-pointer">
                                        <i
                                            class="fa-solid fa-info-circle text-gray-400 ml-2 group-hover:text-blue-500 transition-colors"></i>
                                    </div>
                                    <!-- Tooltip con información completa -->
                                    <div
                                        class="absolute left-0 top-full mt-2 w-72 bg-gray-800 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                        <h4 class="font-semibold text-yellow-400 mb-2">Auditoría de Usuario</h4>
                                        </h4>
                                        <div class="space-y-1">
                                            <div><span class="font-semibold">Creado:</span>
                                                {{ $user->created_at }}
                                            </div>
                                            <div><span class="font-semibold">Actualizado:</span>
                                                {{ $user->updated_at }}
                                            </div>
                                        </div>
                                        <!-- Flecha del tooltip -->
                                        <div class="absolute -top-1 left-4 w-2 h-2 bg-gray-800 transform rotate-45">
                                        </div>
                                    </div>
                                </div>
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
    </div>
@endcomponent

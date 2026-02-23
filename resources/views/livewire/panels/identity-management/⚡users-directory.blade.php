<?php

use Livewire\Component;
use app\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    public $name;
    public $email;
    public $password;
    public $role_check;

    public function with()
    {
        $this->dispatch('saved');
        return [
            'users' => User::select('id', 'name', 'email', 'created_at', 'updated_at')->toBase()->paginate(10),
        ];
    }
    protected function rules()
    {
        return [
            'name' => 'required|min:3|string',
            'email' => 'required|min:4|email',
            'password' => ['required', 'min:8', Password::min(8)->mixedCase()]
        ];
    }
    public function clear()
    {
        $this->reset(
            'name',
            'email',
            'password',
            'role_check'
        );
    }
    public function create()
    {
        $this->validate();
        $request = new Request();
        $request->merge([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role_check' => $this->role_check,
        ]);
        $this->js("console.log('entro a crear usuario')");
    }
};
?>

@component('livewire.panels.identity-management.accesos-layout')
<div x-data="{userForm : false}" class="space-y-4 pb-4">
    <div class="flex justify-between items-center">
        <input type="text" placeholder="Buscar usuario"
            class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
        <button @click="userForm = true"
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

    <!-- Formulario de creación -->
    <div x-show="userForm" x-cloak
        x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center ">
        <!-- Overlay de fondo oscuro -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="userForm=false; $wire.clear()">
        </div>
        <div
            class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50  duration-300">
            <!-- Header modal -->
            <div class="p-6 rounded-t-lg bg-slate-950">
                <h3 class="text-xl font-medium text-center text-white pb-2">Creación de Usuarios</h3>
            </div>
            <!-- Body modal -->
            <form wire:submit="create" class="max-w-full p-6">
                <div class="flex min-w-[200px] gap-4">
                    <div class="mb-5 flex-1">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-9xt-">Nombre</label>
                        <input wire:model="name" type="text" id="name"
                            class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                            placeholder="Ej: Juan Perez" />
                        <span>
                            <x-input-error :messages="$errors->get('name')" />
                        </span>

                    </div>
                    <div class="mb-5 flex-1">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-9xt-">Correo
                            Electronico</label>
                        <input wire:model="email" type="email" id="email"
                            class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                            placeholder="Ej: example@example.com" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                </div>
                <div class="mb-5 flex gap-4">
                    <div class="flex-1">
                        <label for="role_select" class="block mb-2 text-sm font-medium text-gray-900">
                            Seleccionar Rol
                        </label>

                        <select wire:model="role_check" id="role_select"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Selecciona un rol</option>

                            <option value="Prueba" class="">
                            </option>
                        </select>

                        <x-input-error :messages="$errors->get('role_check')" />
                    </div>
                </div>
                <div class="mb-5 flex-1">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-9xt-">Contraseña</label>
                    <input wire:model="password" type="password" id="password"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-slate-950 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " />
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <!-- Footer buttons modal -->
                <div class="w-full flex flex-1 justify-center pt-4">
                    <div class="flex flex-1 gap-4">
                        <button type="button" @click="userForm=false; $wire.clear()"
                            class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                            Cancelar
                        </button>
                        <x-primary-button>
                            Crear Usuario
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcomponent
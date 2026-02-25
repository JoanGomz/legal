<?php

use Livewire\Component;
use App\Http\Controllers\Admin\RoleController;
new class extends Component {
    public $name;
    public $email;
    public $password;
    public $password_verify;
    public $role_check;
    public $roles;
    protected function rules()
    {
        return [
            'name' => 'required|min:3|string',
            'email' => 'required|min:4|email',
            'password' => ['required', 'min:8', Password::min(8)->mixedCase()],
            'password_verify' => 'required,same:password',
        ];
    }
    public function clear()
    {
        $this->reset('name', 'email', 'password', 'role_check');
    }
    public function sendPetition()
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
    public function mount()
    {
        $this->roles = app(RoleController::class)->index();
    }
};
?>

<div x-show="userForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-user-modal.window="userForm = false; $wire.clear()">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="userForm=false; $wire.clear()">
    </div>
    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50  duration-300">
        <!-- Header modal -->
        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>
        <!-- Body modal -->
        <form wire:submit="sendPetition(method)" class="max-w-full p-6">
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
                        @if ($roles['data']['roles'])
                            <option value="">Selecciona un rol</option>
                        @endif
                        @forelse($roles['data']['roles'] as $item)
                            <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                        @empty
                            <option value="">No hay opciones disponibles</option>
                        @endforelse
                    </select>

                    <x-input-error :messages="$errors->get('role_check')" />
                </div>
            </div>
            <div class="mb-5 flex-1">
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-9xt-">Contraseña</label>
                    <input wire:model="password" type="password" id="password" autocomplete="new-password"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-slate-950 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " />
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <div>
                    <label for="password_verify" class="block mb-2 text-sm font-medium text-gray-9xt-">Repite la
                        contraseña</label>
                    <input wire:model="password_verify" type="password" id="password_verify" autocomplete="new-password"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-slate-950 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " />
                    <x-input-error :messages="$errors->get('password')" />
                </div>
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex flex-1 justify-center pt-4">
                <div class="flex flex-1 gap-4">
                    <button type="button" @click="userForm=false; $wire.clear()"
                        class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <x-primary-button x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

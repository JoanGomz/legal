<?php

use Livewire\Component;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Base\BrandsController;
use App\Http\Controllers\Operation\ParksController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use App\Models\User;

new class extends Component {
    use traitCruds;

    public $userId;
    public $name;
    public $email;
    public $park_id;
    public $brand_id;
    public $password;
    public $password_verify;
    public $role_check;

    public function mount()
    {
        $this->brand_id = auth()->user()->brand_id;
    }

    public function rolesBloqueados(): array
    {
        return auth()->user()->hasRole('SuperAdmin') ? [] : ['SuperAdmin', 'Gestor'];
    }

    protected function rules()
    {
        return [
            'name'            => 'required|min:3|string',
            'email'           => 'required|min:4|email',
            'park_id'         => 'required|exists:parks,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'role_check'      => 'required|string',
            'password'        => ['required', Password::min(8)->mixedCase()],
            'password_verify' => 'required|same:password',
        ];
    }

    public function rulesOnly()
    {
        return [
            'name'            => 'required|min:3|string',
            'email'           => 'required|min:4|email',
            'park_id'         => 'required|exists:parks,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'role_check'      => 'required|string',
            'password'        => ['nullable', Password::min(8)->mixedCase()],
            'password_verify' => 'required_with:password|same:password',
        ];
    }

    public function clear()
    {
        $this->reset('userId', 'name', 'email', 'park_id', 'password', 'password_verify', 'role_check');
        $this->brand_id = auth()->user()->brand_id;
        $this->resetValidation();
    }
    #[On('setEditingUser')]
    public function setEditingUser($id)
    {
        $user = User::select('id', 'name', 'email', 'park_id', 'brand_id')
            ->with('roles:id,name')
            ->findOrFail($id);

        $this->resetValidation();
        $this->userId     = $user->id;
        $this->name       = $user->name;
        $this->email      = $user->email;
        $this->park_id    = $user->park_id;
        $this->brand_id   = $user->brand_id;
        $this->role_check = $user->roles->first()?->name;
        $this->reset('password', 'password_verify');

        $this->js("window.prepareModal('update', 'Actualizar Usuario')");
    }

    public function sendPetition($type)
    {
        $isCreate = $type === 'create';

        $isCreate ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();

        try {
            $dataRequest = [
                'name'       => $this->name,
                'email'      => $this->email,
                'role_check' => $this->role_check,
                'park_id'    => (int) $this->park_id,
                'brand_id'   => $this->brand_id ?: null,
            ];

            if ($this->password) {
                $dataRequest['password'] = $this->password;
            }

            $request = new \Illuminate\Http\Request($dataRequest);

            $response = $isCreate
                ? app(UserController::class)->store($request)
                : app(UserController::class)->update($request, $this->userId);

            $this->response = $response instanceof \Illuminate\Http\JsonResponse
                ? $response->getData(true)
                : $response;

            if (($this->response['status'] ?? null) === 'success') {
                $this->dispatch('close-user-modal');
                $this->refreshData();
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $isCreate
                ? 'Ocurrió un error al crear el usuario'
                : 'Ocurrió un error al actualizar el usuario';
            $this->handleException($th, $message);
        }
    }

    public function refreshData()
    {
        $this->dispatch('refresh-user-list')->to('panels.identity-management.users-directory');
    }

    public function with()
    {
        $brand = app(BrandsController::class)->indexPaginated($this->page, $this->perPage, $this->search);

        return [
            'brand' => $brand,
            'roles'  => app(RoleController::class)->index(),
            'parks'  => app(ParksController::class)->index(),
        ];
    }
};
?>

<div x-show="userForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center p-4"
    @close-user-modal.window="userForm = false; $wire.clear()">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="userForm = false; $wire.clear()"></div>

    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50 duration-300">
        <!-- Header modal -->
        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>
        <!-- Body modal -->
        <form @submit.prevent="$wire.sendPetition(method)" class="max-w-full p-6">
            <div class="flex min-w-[200px] gap-4">
                <div class="mb-5 flex-1">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
                    <input wire:model="name" type="text" id="name"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: Juan Pérez" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="mb-5 flex-1">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Correo electrónico</label>
                    <input wire:model="email" type="email" id="email" autocomplete="username"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: example@example.com" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="mb-5">
                <label for="park_select" class="block mb-2 text-sm font-medium text-gray-900">Seleccionar parque</label>
                <select wire:model="park_id" id="park_select"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Elija una opción</option>
                    @foreach ($parks['data'] ?? [] as $item)
                    <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('park_id')" />
            </div>

            <div class="mb-5">
                <label for="brand_select" class="block mb-2 text-sm font-medium text-gray-900">Seleccionar marca</label>
                <select wire:model="brand_id" id="brand_select"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Elija una opción</option>
                    @foreach ($brand['data'] as $item)
                    <option value="{{$item['id']}}"> {{ $item['name'] }} </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('brand_id')" />
            </div>

            <div class="mb-5">
                @php $bloqueados = $this->rolesBloqueados(); @endphp
                <label for="role_select" class="block mb-2 text-sm font-medium text-gray-900">Seleccionar rol</label>
                <select wire:model="role_check" id="role_select"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @forelse ($roles['data']['roles'] ?? [] as $item)
                    <option value="{{ $item['name'] }}" @disabled(in_array($item['name'], $bloqueados))>
                        {{ $item['name'] }}
                    </option>
                    @empty
                    <option value="">No hay opciones disponibles</option>
                    @endforelse
                </select>
                <x-input-error :messages="$errors->get('role_check')" />
            </div>

            <div class="mb-5 space-y-4">
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900">
                        Contraseña
                        <span x-show="method === 'update'" class="font-normal text-gray-400">(déjala vacía para no
                            cambiarla)</span>
                    </label>
                    <input wire:model="password" type="password" id="password" autocomplete="new-password"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-slate-950 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <div>
                    <label for="password_verify" class="block mb-2 text-sm font-medium text-gray-900">Repite la
                        contraseña</label>
                    <input wire:model="password_verify" type="password" id="password_verify" autocomplete="new-password"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-slate-950 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                    <x-input-error :messages="$errors->get('password_verify')" />
                </div>
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex gap-4 pt-4">
                <button type="button" @click="userForm = false; $wire.clear()"
                    class="flex-1 bg-gray-400 rounded-lg text-slate-950 hover:bg-gray-200 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <x-primary-button class="flex-1 justify-center" x-text="textButton"></x-primary-button>
            </div>
        </form>
    </div>
</div>
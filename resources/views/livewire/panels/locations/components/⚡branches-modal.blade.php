<?php

use App\Http\Controllers\Operation\ParksController;
use App\Models\Base\Cities;
use App\Models\Operation\Parks;
use Livewire\Component;
use App\Traits\traitCruds;

new class extends Component {
    use traitCruds;
    public $id;
    public $name;
    public $address;
    public $city;
    public $cities;
    public $type;
    public $capacity;
    protected function rules()
    {
        return [
            'name' => 'required|min:3|string',
            'address' => 'required|min:4',
            'city' => 'required|int',
            'type' => 'required',
            'capacity' => 'required'
        ];
    }
    public function rulesOnly()
    {
        return [
            'name' => 'required|min:3|string',
            'address' => 'required|min:4',
            'city' => 'required|int',
            'type' => 'required',
            'capacity' => 'required|int|min:0'
        ];
    }
    public function clear()
    {
        $this->reset('name', 'address', 'city', 'type', 'capacity');
    }
    // #[On('setEditingUser')]
    // public function setEditingUser($id)
    // {
    //     $user = User::select('id', 'name', 'email')->findOrFail($id);
    //     $this->id = $user->id;
    //     $this->name = $user->name;
    //     $this->address = $user->email;
    //     $this->js("window.prepareModal('update', 'Actualizar Usuario')");
    // }
    public function sendPetition($type)
    {
        $type == 'create' ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();
        try {
            $dataRequest = [
                'name' => $this->name,
                'location' => $this->address,
                'city' => $this->city,
                'type' => $this->type,
                'capacity' => $this->capacity
            ];
            $request = new \Illuminate\Http\Request();
            $request->merge($dataRequest);
            $this->response = $type == 'create' ? app(ParksController::class)->store($request) : app(ParksController::class)->update($request, $this->id);
            if ($this->response['status'] == 'success') {
                $this->dispatch('close-sede-modal');
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $type == 'create' ? 'Ocurrio un error al crear la sede' : 'Ocurrio un error al actualizar la sede';
            $this->handleException($th, $message);
        }
    }
    public function refreshData()
    {
        $this->dispatch('refresh-user-list')->to('panels.identity-management.users-directory');
    }
    public function mount()
    {
        $this->cities = \App\Models\Base\Cities::all();
    }
};
?>

<div x-show="sedeForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-user-modal.window="userForm = false; $wire.clear()">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="sedeForm=false; $wire.clear()">
    </div>
    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50  duration-300">
        <!-- Header modal -->
        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>
        <!-- Body modal -->
        <form @submit.prevent="$wire.sendPetition(method)" class="max-w-full p-6">
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
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-9xt-">Dirección</label>
                    <input wire:model="address" id="email"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                        placeholder="Ej: calle 72 b" />
                    <x-input-error :messages="$errors->get('address')" />
                </div>
            </div>
            <div class="mb-5 flex gap-4">
                <div class="flex-1">
                    <label for="role_select" class="block mb-2 text-sm font-medium text-gray-900">
                        Seleccionar Una ciudad
                    </label>

                    <select wire:model="city" id="role_select"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Selecciona una opcion</option>
                        @foreach ($cities as $city )
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach

                    </select>

                    <x-input-error :messages="$errors->get('city')" />
                </div>
                <div class="mb-5 flex-1">
                    <label for="role_select" class="block mb-2 text-sm font-medium text-gray-900">
                        Selecciona el Tipo de sede
                    </label>

                    <select wire:model="type" id="role_select"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                        <option value="">Selecciona una opcion</option>
                        <option value="parque">Parque</option>
                        <option value="centro comercial">Centro Comercial</option>

                    </select>
                    <x-input-error :messages="$errors->get('type')" />
                </div>

            </div>
            <div class="mb-5 flex-1">
                <label for="capacidad" class="block mb-2 text-sm font-medium text-gray-9xt-">Capacidad</label>
                <input wire:model="capacity"
                    class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                    placeholder="Ej: 20" />
                <x-input-error :messages="$errors->get('capacity')" />
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex flex-1 justify-center pt-4">
                <div class="flex flex-1 gap-4">
                    <button type="button" @click="sedeForm=false; $wire.clear()"
                        class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <x-primary-button x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
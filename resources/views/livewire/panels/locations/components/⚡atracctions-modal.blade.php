<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Http\Controllers\Operation\ParksController;
use App\Models\Operation\AtraccionArcade;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use traitCruds;
    public $id;
    public $name;
    public $description;
    public $type;
    public $capacity;
    public $consume;
    public $time;
    public $serial;
    public $mac;
    public $parque;
    public $ubication;
    public $state;

    public function clear()
    {
        $this->reset();
    }
    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'capacity' => 'required|int|min:0',
            'consume' => 'required|int|min:0',
            'time' => 'required|min:0',
            'serial' => 'required|string',
            'mac' => 'required|string|min:0',
            'parque' => 'required|int|min:0',
            'ubication' => 'required|string|min:0',
            'state' => 'required'
        ];
    }
    public function rulesOnly()
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'capacity' => 'required|int|min:0',
            'consume' => 'required|int|min:0',
            'time' => 'required|min:0',
            'serial' => 'required|string',
            'mac' => 'required|string|min:0',
            'parque' => 'required|int|min:0',
            'ubication' => 'required|string|min:0',
            'state' => 'required'
        ];
    }

    #[On('setEditingAtracction')]
    public function setEditingAtracction($data)
    {
        $atracction = AtraccionArcade::findOrFail($data['id']);

        $this->id          = $atracction->id;
        $this->name        = $data['name'];
        $this->description = $data['descripcion'];
        $this->type        = $data['type'];
        $this->capacity    = $data['capacity'];
        $this->consume     = $data['consume'];
        $this->state       = $data['state'];
        $this->ubication   = $data['ubication'];
        $this->parque      = $data['parque'];
        $this->serial      = $data['serial'];
        $this->mac         = $data['mac'];
        if (!empty($data['time'])) {
            $partes = explode(':', $data['time']);
            $this->time = isset($partes[1]) ? (int)$partes[1] : (int)$data['time'];
        }


        $this->js("window.prepareModal('update', 'Actualizar Atracciones')");
        $this->dispatch('open-modal-edit');
    }

    public function sendPetition($type)
    {
        $type == 'create' ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();
        try {
            $dataRequest = [
                'nombre' => $this->name,
                'descripcion' => $this->description,
                'tipo' => $this->type,
                'capacidad' => $this->capacity,
                'promedio_consumo' => $this->consume,
                'tiempo_juego' => '00:' . (strlen($this->time) === 1 ? ('0' . $this->time) : $this->time) . ':00',
                'numero_serie' => $this->serial,
                'device_mac' => $this->mac,
                'id_park' => $this->parque,
                'ubicacion' => $this->ubication,
                'estado' => $this->state,

            ];
            $request = new \Illuminate\Http\Request();
            $request->merge($dataRequest);
            $this->response = $type == 'create' ? app(AtraccionArcadeController::class)->store($request) : app(AtraccionArcadeController::class)->update($request, $this->id);

            if ($this->response['status'] == 'success') {
                $this->dispatch('close-atracction-modal');
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $type == 'create' ? 'Ocurrio un error al crear la sede' : 'Ocurrio un error al actualizar la sede';
            $this->handleException($th, $message);
        }
    }
    public function refreshData()
    {
        $this->dispatch('refresh-atracction-list')->to('panels.locations.atracctions');
    }
    public function with()
    {

        $parques = app(ParksController::class)->index();
        return [
            'parques' => $parques
        ];
    }
};
?>

<div x-show="atracctionForm" x-cloak x-transition:enter="transition ease-out duration-200 delay-100"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center"
    @close-atracction-modal.window="atracctionForm = false; $wire.clear()">

    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="atracctionForm = false; $wire.clear()">
    </div>

    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50 duration-300">

        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>

        <form @submit.prevent="$wire.sendPetition(method)" class="max-w-full p-6">

            <div class="flex flex-col sm:flex-row gap-4 mb-5">
                <div class="flex-1">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nombre</label>
                    <input wire:model="name" type="text" id="name"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: Montaña Rusa" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="flex-1">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <input wire:model="description" id="description"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Breve descripción..." />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-5">
                <div class="flex-1">
                    <label for="type_select" class="block mb-2 text-sm font-medium text-gray-900">Tipo</label>
                    <select wire:model="type" id="type_select"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Selecciona una opción</option>
                        <option value="atraccion">Atracción</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" />
                </div>
                <div class="flex-1">
                    <label for="capacity" class="block mb-2 text-sm font-medium text-gray-900">Capacidad</label>
                    <input wire:model="capacity" id="capacity" type="number"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: 10" />
                    <x-input-error :messages="$errors->get('capacity')" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-5">
                <div class="flex-1">
                    <label for="consume" class="block mb-2 text-sm font-medium text-gray-900">Promedio Consumo</label>
                    <input wire:model="consume" id="consume"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: 50kw" />
                    <x-input-error :messages="$errors->get('consume')" />
                </div>
                <div class="flex-1">
                    <label for="time" class="block mb-2 text-sm font-medium text-gray-900">Tiempo de Juego (min)</label>
                    <input wire:model="time" id="time" type="number"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Ej: 10" />
                    <x-input-error :messages="$errors->get('time')" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-5">
                <div class="flex-1">
                    <label for="serial" class="block mb-2 text-sm font-medium text-gray-700">Número de Serie</label>
                    <input wire:model="serial" id="serial"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="SN-000000" />
                    <x-input-error :messages="$errors->get('serial')" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-5">
                <div class="flex-1">
                    <label for="mac" class="block mb-2 text-sm font-medium text-gray-700">Mac</label>
                    <input wire:model="mac" type="text" id="mac"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="00:00:00..." />
                    <x-input-error :messages="$errors->get('mac')" />
                </div>
                <div class="flex-1">
                    <label for="parque" class="block mb-2 text-sm font-medium text-gray-900">Parque</label>
                    <select wire:model="parque" id="parque"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Selecciona una opción</option>
                        @foreach ($parques['data'] as $parque )
                        <option value="{{$parque->id }}">{{ $parque->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('parque')" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                <div class="flex-1">
                    <label for="ubication" class="block mb-2 text-sm font-medium text-gray-700">Ubicación</label>
                    <input wire:model="ubication" type="text" id="ubication"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        placeholder="Zona A, Nivel 1" />
                    <x-input-error :messages="$errors->get('ubication')" />
                </div>
                <div class="flex-1">
                    <label for="state" class="block mb-2 text-sm font-medium text-gray-900">Estado</label>
                    <select wire:model="state" id="state"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Selecciona una opción</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                    <x-input-error :messages="$errors->get('state')" />
                </div>
            </div>

            <div class="flex gap-4">
                <button type="button" @click="atracctionForm = false; $wire.clear()"
                    class="flex-1 px-4 py-2 bg-gray-400 rounded-lg text-slate-900 font-bold hover:bg-gray-300 transition duration-150">
                    Cancelar
                </button>
                <div class="flex-1">
                    <x-primary-button class="w-full justify-center py-2" x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php

use App\Http\Controllers\Base\BrandsController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use traitCruds;
    public $name;
    public $id;
    public $description;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:250',
            'description' => 'required|string|max:250'
        ];
    }
    protected function rulesOnly()
    {
        return [
            'name' => 'string|max:250',
            'description' => 'string|max:250'
        ];
    }
    #[On('setEditingBrand')]
    public function setEditingBrand($id, $name, $description)
    {
        $this->id = $id;
        $this->name = $name ?? null;
        $this->description = $description ?? null;

        $this->js("window.prepareModal('update', 'Actualizar Brand')");
    }
    public function clear()
    {
        $this->reset(['name', 'description']);
    }
    public function sendPetition($type)
    {
        $isCreate = $type === 'create';

        $isCreate ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();

        try {
            $request = new \Illuminate\Http\Request([
                'name'        => $this->name,
                'description' => $this->description,
            ]);

            $controller = app(BrandsController::class);
            $response = $isCreate
                ? $controller->store($request)
                : $controller->update($request, $this->id);

            $this->response = $response instanceof \Illuminate\Http\JsonResponse
                ? $response->getData(true)
                : $response;

            if (($this->response['status'] ?? null) === 'success') {
                $this->dispatch('close-brand-modal');
            }

            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $isCreate
                ? 'Ocurrió un error al crear la marca'
                : 'Ocurrió un error al actualizar la marca';
            $this->handleException($th, $message);
        }
    }
};
?>

<div x-show="brandForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-brand-modal.window="brandForm = false; $wire.clear()">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="brandForm=false; $wire.clear()">
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
                        placeholder="Ej: Tu marca" />
                    <span>
                        <x-input-error :messages="$errors->get('name')" />
                    </span>

                </div>
                <div class="mb-5 flex-1">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-9xt-">Descripción</label>
                    <input wire:model="description" id="email"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                        placeholder="Ej: Describe lo que hace tu brand" />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex flex-1 justify-center pt-4">
                <div class="flex flex-1 gap-4">
                    <button type="button" @click="brandForm=false; $wire.clear()"
                        class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <x-primary-button x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php

use Livewire\Component;
use App\Traits\traitCruds;
use App\Http\Controllers\Admin\PermissionController;
use Livewire\Attributes\On;
new class extends Component {
    use traitCruds;
    //MODELOS DE FORMULARIOS
    public $id;
    public $name;

    //METODOS VARIOS
    //METODO DE VALIDACIONES
    protected function rules()
    {
        return [
            'name' => 'required|min:3',
        ];
    }
    public function clear()
    {
        $this->reset('id', 'name');
        $this->response = '';
    }
    public function refreshData()
    {
        $this->dispatch('refresh-user-list')->to('panels.identity.management.permissions');
    }
    //METODO PARA ESTABLECER LOS VALORES DEL UPDATE Y MOSTRARLOS EN EL FORMULARIO
    #[On('setEditingPermission')]
    public function setEditingPermission($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->js("window.prepareModal('update', 'Actualizar Permiso')");
    }
    //METODO PARA CREAR PERMISOS
    public function sendPetition($type)
    {
        // if (!$this->validarPermiso('permission.create')) {
        //     return;
        // }
        $this->validateWithSpinner();
        try {
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'name' => $this->name,
            ]);
            $this->response = $type == 'create' ? app(PermissionController::class)->store($request) : app(PermissionController::class)->update($request, $this->id);

            if ($this->response['status'] == 'success') {
                $this->dispatch('close-permission-modal');
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $type == 'create' ? 'Error al crear el permiso f' : 'Error al actualizar el permiso';
            $this->handleException($th, $message);
        }
    }
};
?>

<div x-show="permissionForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-permission-modal.window="permissionForm = false">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="permissionForm=false; $wire.clear()">
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
                        placeholder="Ej: user.create" />
                    <span>
                        <x-input-error :messages="$errors->get('name')" />
                    </span>
                </div>
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex flex-1 justify-center pt-4">
                <div class="flex flex-1 gap-4">
                    <button type="button" @click="permissionForm=false; $wire.clear()"
                        class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <x-primary-button x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

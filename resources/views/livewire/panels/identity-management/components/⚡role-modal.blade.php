<?php

use Livewire\Component;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;
new class extends Component {
    use traitCruds;
    public $id;
    public $name;
    public $description;
    public $selectedPermissions = [];
    public function clear()
    {
        $this->reset(['name','description','selectedPermissions']);
    }
    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'description' => 'required|min:4',
            'selectedPermissions' => 'required',
        ];
    }
    public function with()
    {
        $permissions = app(PermissionController::class)->index();
        return [
            'permissions' => $permissions,
        ];
    }
    public function refreshData()
    {
        $this->dispatch('refresh-user-list')->to('panels.identity-management.roles');
    }
    //METODO PARA ESTABLECER LOS VALORES DEL UPDATE Y MOSTRARLOS EN EL FORMULARIO
    #[On('setEditingRole')]
    public function setEditingRole($id)
    {
        $role = Role::select('id', 'name','description')->findOrFail($id);
        $this->id = $role['id'];
        $this->name = $role['name'];
        $this->description = $role['description'];
        $this->selectedPermissions = $role->permissions()->allRelatedIds()->toArray();
        $this->js("window.prepareModal('update', 'Actualizar Rol')");
    }
    //METODO PARA CREAR PERMISOS
    public function sendPetition($type)
    {
        // if (!$this->validarPermiso('permission.create')) {
        //     return;
        // }
        $this->validateWithSpinner();
        try {
            $this->selectedPermissions = array_map('intval', $this->selectedPermissions);
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'name' => $this->name,
                'description' => $this->description,
                'permissions' => $this->selectedPermissions,
            ]);
            $this->response = $type == 'create' ? app(RoleController::class)->store($request) : app(RoleController::class)->update($request, $this->id);

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

<div x-show="roleForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-permission-modal.window="roleForm = false">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="roleForm=false; $wire.clear()">
    </div>
    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50  duration-300">
        <!-- Header modal -->
        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>
        <!-- Body modal -->
        <form @submit.prevent="$wire.sendPetition(method)" class="max-w-full p-6">
            <div class="flex min-w-[200px] gap-4 flex-col">
                <div class="mb-5 flex-1">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-9xt-">Nombre</label>
                    <input wire:model="name" type="text" id="name"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                        placeholder="Ej: Gestor Jurídico" />
                    <span>
                        <x-input-error :messages="$errors->get('name')" />
                    </span>
                </div>
                <div class="mb-5 flex-1">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-9xt-">Descripción</label>
                    <input wire:model="description" type="text" id="description"
                        class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "
                        placeholder="Ej: Visualizador de autorizaciones" />
                    <span>
                        <x-input-error :messages="$errors->get('description')" />
                    </span>
                </div>
                <div class="mb-5 flex-1">
                    <div class="space-y-1 max-h-[26rem] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse ($permissions as $item)
                            <label wire:key="perm-{{ $item['id'] }}" for="permission-{{ $item['id'] }}"
                                class="group flex items-center p-2 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors duration-150">
                                <div class="relative flex items-center">
                                    <input type="checkbox" id="permission-{{ $item['id'] }}"
                                        value="{{ $item['id'] }}" wire:model.live="selectedPermissions"
                                        class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-slate-500 checked:bg-blue-600 checked:border-blue-600 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 pointer-events-none"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <span
                                    class="ml-3 text-sm font-medium text-slate-300 group-hover:text-white transition-colors">
                                    {{ $item['name'] }}
                                </span>
                            </label>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-slate-500">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs uppercase tracking-widest">Sin resultados</p>
                            </div>
                        @endforelse
                    </div>
                    <span>
                        <x-input-error :messages="$errors->get('name')" />
                    </span>
                </div>
            </div>
            <!-- Footer buttons modal -->
            <div class="w-full flex flex-1 justify-center pt-4">
                <div class="flex flex-1 gap-4">
                    <button type="button" @click="roleForm=false; $wire.clear()"
                        class="bg-gray-400 rounded-lg text-slate-950 w-full hover:bg-gray-200 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <x-primary-button x-text="textButton"></x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

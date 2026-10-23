<?php

use Livewire\Component;
use App\Http\Controllers\Operation\ClientController;
use App\Http\Controllers\Operation\ParksController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use App\Models\User;

new class extends Component {
    use traitCruds;
    public $id;
    public $name;
    public $last_name;
    public $gender;
    public $birth_date;
    public $document_type;
    public $document_number;
    public $phone;
    public $address;
    public $email;
    public $park_id;
    public $park;
    public $password;
    public $password_verify;
    public $role_check;
    public $roles;

    protected function rules()
    {
        return [
            'name' => 'required|string',
            'last_name' => 'required|string',
            'document_number' => 'nullable|string|min:5|max:20',
            'document_type' => 'nullable|in:CC,CE,PS',
            'gender' => 'nullable|string',
            'phone' => 'required|numeric|digits_between:7,15',
            'email' => 'nullable|email',
            'park_id' => 'required',
        ];
    }
    protected function rulesOnly()
    {
        return [
            'name' => 'required|string',
            'last_name' => 'required|string',
            'document_number' => 'nullable|string|min:5|max:20',
            'document_type' => 'nullable|in:CC,CE,PS',
            'gender' => 'nullable|string',
            'phone' => 'required|numeric|digits_between:7,15',
            'email' => 'nullable|email',
            'park_id' => 'required',
        ];
    }
    public function clear()
    {
        $this->reset('name', 'email', 'password', 'role_check');
    }
    #[On('setEditingClient')]
    public function setEditingClient($id)
    {
        $this->id = $id['id'];
        $this->name = $id['first_name'] ?? null;
        $this->last_name = $id['last_name'] ?? null;
        $this->email = $id['email'] ?? null;
        $this->phone = $id['phone'] ?? null;
        $this->address = $id['address'] ?? null;
        $this->document_number = $id['document_number'] ?? null;
        $this->document_type = $id['document_type'] ?? null;
        $this->park_id = $id['parks_id'] ?? null;
        //$this->role_check = $user->roles->first()->name;
        $this->js("window.prepareModal('update', 'Actualizar Usuario')");
    }
    public function sendPetition($type)
    {
        $type = $type = 'create' ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();
        try {
            $dataRequest = [
                'first_name' => $this->name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'document_number' => $this->document_number,
                'document_type' => $this->document_type,
                'email' => $this->email,
                'role_check' => $this->role_check,
                'parks_id' => (int)$this->park_id,
            ];
            $request = new \Illuminate\Http\Request();
            $request->merge($dataRequest);
            $this->response = $type == 'create' ? app(ClientController::class)->store($request) : app(ClientController::class)->update($request, $this->id);
            if ($this->response['status'] == 'success') {
                $this->dispatch('close-client-modal');
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $type == 'create' ? 'Ocurrio un error al crear el usuario' : 'Ocurrio un error al actualizar el usuario';
            $this->handleException($th, $message);
        }
    }
    public function refreshData()
    {
        $this->dispatch('refresh-client-list')->to('panels.identity-management.users-directory');
    }
    public function mount()
    {
        $this->park = app(ParksController::class)->index();
    }
};
?>
@php
$input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900
placeholder:text-slate-400 shadow-sm transition focus:border-[#FD7A0F] focus:outline-none focus:ring-2
focus:ring-[#FD7A0F]/30';
$label = 'mb-1.5 block text-sm font-medium text-slate-700';
$trigger = 'flex w-full items-center justify-between rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-left
text-sm text-slate-900 shadow-sm transition hover:border-slate-400 focus:border-[#FD7A0F] focus:outline-none
focus:ring-2 focus:ring-[#FD7A0F]/30';
$panel = 'absolute z-30 mt-1.5 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg';
$option = 'flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-slate-700 transition-colors
hover:bg-orange-50 hover:text-[#FD7A0F]';
@endphp

<div x-show="clientForm" x-cloak
    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center "
    @close-client-modal.window="clientForm = false; $wire.clear()">
    <!-- Overlay de fondo oscuro -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="clientForm=false; $wire.clear()">
    </div>
    <div
        class="relative bg-white rounded-lg shadow-xl mx-auto max-w-lg w-full transform transition-all z-50  duration-300">
        <!-- Header modal -->
        <div class="p-6 rounded-t-lg bg-slate-950">
            <h3 class="text-xl font-medium text-center text-white pb-2" x-text="titleModal"></h3>
        </div>
        <!-- Body modal -->
        <form @submit.prevent="$wire.sendPetition(method)" class="flex min-h-0 flex-1 flex-col">

            <div class="flex-1 space-y-8 overflow-y-auto px-6 py-6">

                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Datos personales</h4>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="{{ $label }}">Nombre</label>
                            <input wire:model="name" type="text" id="name" class="{{ $input }}" placeholder="Ej: Ana" />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <label for="last_name" class="{{ $label }}">Apellido</label>
                            <input wire:model="last_name" type="text" id="last_name" class="{{ $input }}"
                                placeholder="Ej: Garcia" />
                            <x-input-error :messages="$errors->get('last_name')" />
                        </div>

                        <div>
                            <label for="gender" class="{{ $label }}">Género</label>
                            <select wire:model="gender" id="gender"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Elija una opción</option>
                                <option value="male">Masculino</option>
                                <option value="female">Femenino</option>
                                <option value="other">Otro</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" />
                        </div>
                        <div>
                            <label for="birth_date" class="{{ $label }}">Fecha de nacimiento</label>
                            <input wire:model="birth_date" type="date" id="birth_date" class="{{ $input }}" />
                            <x-input-error :messages="$errors->get('birth_date')" />
                        </div>

                        {{-- Tipo de documento --}}
                        <div x-data="{
                                open: false,
                                documentType: @entangle('document_type'),
                                options: [
                                    { value: 'CC', label: 'Cédula Ciudadana' },
                                    { value: 'CE', label: 'Cédula de Extranjería' },
                                    { value: 'TI', label: 'Tarjeta de Identidad' },
                                    { value: 'PASSPORT', label: 'Pasaporte' },
                                    { value: 'NIT', label: 'Número de Identificación Tributaria' },
                                    { value: 'OTHER', label: 'Otro' },
                                ],
                                get selectedLabel() {
                                    const found = this.options.find(o => o.value === this.documentType);
                                    return found ? found.label : 'Elija una opción';
                                },
                                select(option) {
                                    this.documentType = option.value;
                                    this.open = false;
                                }
                            }" @click.outside="open = false">

                            <label for="type_document" class="{{ $label }}">Tipo de documento</label>

                            <div class="relative">
                                <button type="button" id="type_document" @click="open = !open" class="w-full flex items-center justify-between text-left
                                    bg-white/30 backdrop-blur-md border border-white/40
                                    text-gray-900 text-sm rounded-xl shadow-lg shadow-black/5
                                    p-2.5 pr-3
                                    transition-all duration-200 ease-in-out
                                    hover:bg-white/40 hover:border-white/60
                                    focus:outline-none focus:ring-2 focus:ring-blue-400/60"
                                    :class="open && 'ring-2 ring-blue-400/60 bg-white/40'">
                                    <span x-text="selectedLabel" :class="!documentType && 'text-gray-500'"></span>
                                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 flex-shrink-0 ml-2"
                                        :class="open && 'rotate-180'" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95" class="absolute z-50 mt-2 w-full
                                    bg-white/40 backdrop-blur-xl border border-white/50
                                    rounded-xl shadow-2xl shadow-black/10 overflow-hidden" style="display: none;">

                                    <ul class="max-h-56 overflow-y-auto py-1">
                                        <template x-for="option in options" :key="option.value">
                                            <li @click="select(option)" class="flex items-center justify-between px-3 py-2 text-sm cursor-pointer
                                    text-gray-800 transition-colors duration-150
                                    hover:bg-blue-500/10"
                                                :class="documentType === option.value && 'bg-blue-500/15 font-medium'">
                                                <span x-text="option.label"></span>
                                                <svg x-show="documentType === option.value"
                                                    class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <x-input-error :messages="$errors->get('document_type')" />
                        </div>
                        <div>
                            <label for="document_number" class="{{ $label }}">Número de documento</label>
                            <input wire:model="document_number" type="text" id="document_number" class="{{ $input }}"
                                placeholder="Ej: 1015478545" />
                            <x-input-error :messages="$errors->get('document_number')" />
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contacto y ubicación
                        </h4>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="{{ $label }}">N° de WhatsApp</label>
                            <input wire:model="phone" type="text" id="phone" class="{{ $input }}"
                                placeholder="Ej: 1234567890" />
                            <x-input-error :messages="$errors->get('phone')" />
                        </div>
                        <div>
                            <label for="email" class="{{ $label }}">Correo electrónico</label>
                            <input wire:model="email" type="email" id="email" autocomplete="username" novalidate
                                class="{{ $input }}" placeholder="Ej: example@example.com" />
                            <x-input-error :messages="$errors->get('email')" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="{{ $label }}">Dirección</label>
                            <input wire:model="address" type="text" id="address" class="{{ $input }}"
                                placeholder="Ej: Calle 100" />
                            <x-input-error :messages="$errors->get('address')" />
                        </div>
                    </div>
                </section>


                <section class="space-y-4">
                    <div class="mb-5 flex gap-4">
                        <div class="flex-1">
                            <label for="role_select" class="block mb-2 text-sm font-medium text-gray-900">
                                Seleccionar Parque
                            </label>

                            <select wire:model="park_id"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Eliga una opcion</option>
                                @foreach ( $park['data'] as $item )
                                <option value="{{ $item['id'] }}"> {{ $item['name'] }}</option>
                                @endforeach
                            </select>

                            <x-input-error :messages="$errors->get('park_id')" />
                        </div>
                    </div>
            </div>
            </section>

            <div
                class="flex flex-1 shrink-0 items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <button type="button" @click="clientForm=false; $wire.clear()"
                    class="w-full rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    Cancelar
                </button>
                <x-primary-button x-text="textButton" class="w-full justify-center px-6"></x-primary-button>
            </div>
        </form>
    </div>
</div>
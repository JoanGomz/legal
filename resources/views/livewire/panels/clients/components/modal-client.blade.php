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
    public $accepts_promotions;
    public $data_consent;
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
            'data_consent' => 'accepted',
            'accepts_promotions' => 'nullable',
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
        $this->reset('name', 'email', 'last_name', 'document_number', 'document_type', 'gender', 'phone', 'data_consent', 'accepts_promotions', 'park_id');
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
        $this->data_consent = (bool) $id['data_consent'] ?? null;
        $this->accepts_promotions = (bool) $id['accepts_promotions'] ?? null;
        //$this->role_check = $user->roles->first()->name;
        $this->js("window.prepareModal('update', 'Actualizar Usuario')");
    }
    public function sendPetition($type)
    {
        $isCreate = $type === 'create';

        $isCreate ? $this->validateWithSpinner() : $this->validateWithSpinnerUpdate();

        try {
            $dataRequest = [
                'first_name' => $this->name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'document_number' => $this->document_number,
                'document_type' => $this->document_type,
                'email' => $this->email,
                'address' => $this->address,
                'gender' => $this->gender,
                'birth_date' => $this->birth_date,
                'role_check' => $this->role_check,
                'parks_id' => $this->park_id ? (int) $this->park_id : auth()->user()->parks_id,
                'data_consent' => $this->data_consent,
                'accepts_promotions' => $this->accepts_promotions,
            ];
            $request = new \Illuminate\Http\Request();
            $request->merge($dataRequest);
            $this->response = $isCreate
                ? app(ClientController::class)->store($request)
                : app(ClientController::class)->update($request, $this->id);

            if ($this->response['status'] == 'success') {
                $this->dispatch('close-client-modal');
            }
            $this->refreshData();
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = $isCreate ? 'Ocurrió un error al crear el cliente' : 'Ocurrió un error al actualizar el cliente';
            $this->handleException($th, $message);
        }
    }
    public function refreshData()
    {
        $this->dispatch('refresh-client-list')->to('panels.clients.list-clients');
    }
    public function mount()
    {
        $this->park = app(ParksController::class)->index();
        if (auth()->user()->hasRole('Admin')) {
            $this->park_id = $this->park['data'][0]['id'];
        }
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

<div x-show="clientForm" x-cloak x-data="{ policy: false,
                                promotions: false}"
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
                </section>
                <section>
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-start md:items-center space-x-3">
                            <div class="flex-shrink-0">
                                <input type="checkbox" wire:model.live="data_consent"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            </div>

                            <div class="text-xs text-gray-500 leading-normal">
                                <p class="inline">
                                    Acepto y declaro haber leído y comprendido la
                                    <span class="font-semibold text-gray-700">Política de Tratamiento de Datos
                                        Personales</span>
                                    y Condiciones de Uso de Eurema.
                                </p>
                                <button type="button" @click="policy = true"
                                    class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center transition-colors ml-1 font-medium">
                                    <i class="fa-solid fa-file-pdf mx-1"></i>
                                    Ver Política de Privacidad
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('data_consent')" class="mt-2" />

                        <div class="flex items-start md:items-center space-x-3">
                            <div class="flex-shrink-0">
                                <input type="checkbox" wire:model.live="accepts_promotions"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            </div>

                            <div class="text-xs text-gray-500 leading-normal">
                                <p class="inline">
                                    Sí, autorizo que me envíen promociones e invitaciones de Eurema
                                    <span class="font-semibold text-gray-700">Autorización para el uso de datos en el
                                        envío de
                                        promociones
                                    </span>
                                </p>
                                <button type="button" @click="promotions = true"
                                    class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center transition-colors ml-1 font-medium">
                                    <i class="fa-solid fa-file-pdf mx-1"></i>
                                    Ver Política de Promociones
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('accepts_promotions')" class="mt-2" />
                    </div>
                    <div x-show="policy" x-cloak x-effect="document.body.classList.toggle('overflow-hidden', policy)"
                        @keydown.escape.window="policy = false"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog"
                        aria-modal="true" aria-labelledby="politica-titulo">

                        {{-- Fondo --}}
                        <div x-show="policy" x-transition.opacity.duration.200ms
                            class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="policy = false"></div>

                        {{-- Panel --}}
                        <div x-show="policy" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="relative w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl bg-white shadow-2xl shadow-black/30 overflow-hidden">

                            {{-- Cabecera --}}
                            <div class="flex items-start gap-4 px-6 py-5 bg-gradient-to-br from-[#14678C] to-[#0A3C55]">
                                <div
                                    class="w-11 h-11 shrink-0 rounded-full bg-gradient-to-br from-[#E8C987] to-[#B8913F] flex items-center justify-center shadow-md shadow-black/30">
                                    <i class="fa-solid fa-shield-halved text-[#0A3C55]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-[#E8C987]">Ley
                                        1581 de
                                        2012</p>
                                    <h2 id="politica-titulo"
                                        class="text-lg sm:text-xl font-semibold text-white leading-tight">
                                        Política de tratamiento de datos personales
                                    </h2>
                                </div>
                                <button type="button" @click="policy = false" title="Cerrar"
                                    class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-white/70 hover:bg-white/10 hover:text-white transition">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Contenido con scroll --}}
                            <div
                                class="flex-1 overflow-y-auto px-6 py-6 text-sm leading-relaxed text-slate-600 space-y-6">

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">1. Responsable</h3>
                                    <p>Eurema, NIT [por completar], domicilio [dirección y ciudad],
                                        correo [por completar], teléfono [por completar].</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">2. Encargado</h3>
                                    <p>Spoon, NIT [por completar], proveedor de la plataforma NoCard by NEXO, que
                                        registra al
                                        visitante y conserva la evidencia de su aceptación. Spoon trata los datos solo
                                        por
                                        cuenta de Eurema y según sus instrucciones.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">3. Marco legal</h3>
                                    <p>Artículos 15 y 20 de la Constitución Política, Ley Estatutaria 1581 de 2012,
                                        Decreto 1074
                                        de 2015 (que compila el Decreto 1377 de 2013) y demás normas que los modifiquen.
                                    </p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-2">4. Datos que se recolectan</h3>
                                    <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                        <li><span class="font-medium text-slate-700">Del adulto responsable:</span>
                                            nombre,
                                            documento de identidad, número de WhatsApp y, si lo entrega, correo
                                            electrónico.
                                        </li>
                                        <li><span class="font-medium text-slate-700">Del menor:</span> nombre, edad y
                                            estatura,
                                            para verificar los requisitos de la atracción, y la atracción que va a usar.
                                        </li>
                                        <li>Declaración sobre las condiciones médicas, físicas o de salud del menor. Es
                                            un dato
                                            sensible (ver numeral 7).</li>
                                        <li>Evidencia de cada aceptación: fecha, hora, número de WhatsApp y texto
                                            aceptado.</li>
                                    </ul>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-2">5. Finalidades</h3>
                                    <p class="font-medium text-slate-700 mb-1">Finalidades necesarias para el servicio:
                                    </p>
                                    <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                        <li>Registrar al visitante y al menor y verificar los requisitos de acceso a
                                            cada
                                            atracción.</li>
                                        <li>Conservar la prueba del consentimiento informado.</li>
                                        <li>Atender consultas, incidentes y solicitudes durante la activación, por
                                            WhatsApp o
                                            con los asesores de Eurema.</li>
                                        <li>Proteger la seguridad de los visitantes y atender requerimientos de
                                            autoridades
                                            competentes.</li>
                                        <li>Elaborar estadísticas de asistencia de forma agregada, sin identificar a la
                                            persona.
                                        </li>
                                    </ul>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">6. Autorización</h3>
                                    <p>El tratamiento requiere autorización previa, expresa e informada del titular. Se
                                        obtiene
                                        por WhatsApp antes de usar las atracciones, con registro de fecha, hora, número
                                        y texto
                                        aceptado, de modo que pueda consultarse después.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">7. Datos sensibles</h3>
                                    <p>La declaración sobre las condiciones de salud del menor es un dato sensible.
                                        Responderla
                                        es facultativo. Si el adulto prefiere no declararla, Eurema puede negar el uso
                                        de la
                                        atracción por razones de seguridad. Este dato se usa solo para verificar la
                                        aptitud del
                                        menor y la seguridad en la atracción, y nunca para promociones.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">8. Datos de niños, niñas y
                                        adolescentes</h3>
                                    <p>Se tratan solo con autorización del padre, la madre o el representante legal, y
                                        únicamente para registrar al menor, verificar requisitos y proteger su
                                        seguridad. Se
                                        respeta el interés superior del menor y su opinión según su madurez. Los datos
                                        del menor
                                        no se usan para promociones, publicidad ni perfilamiento.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">9. Encargados y proveedores
                                        tecnológicos</h3>
                                    <p>Spoon trata los datos como encargada. El canal de WhatsApp se presta mediante
                                        Meta, y los
                                        servidores y copias de respaldo pueden estar dentro o fuera de Colombia. En
                                        todos los
                                        casos el tratamiento se hace por cuenta de Eurema, bajo contrato de transmisión
                                        de
                                        datos. Eurema no vende los datos personales.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-2">10. Derechos del titular</h3>
                                    <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                        <li>Conocer, actualizar y rectificar sus datos.</li>
                                        <li>Solicitar prueba de la autorización otorgada.</li>
                                        <li>Ser informado sobre el uso que se da a sus datos.</li>
                                        <li>Presentar quejas ante la Superintendencia de Industria y Comercio.</li>
                                        <li>Revocar la autorización o pedir la supresión del dato, cuando proceda.</li>
                                        <li>Acceder gratuitamente a sus datos.</li>
                                        <li>Oponerse en cualquier momento a recibir promociones, sin que eso afecte su
                                            entrada
                                            ni el uso de las atracciones.</li>
                                    </ul>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">11. Cómo ejercer los derechos</h3>
                                    <p>Por [correo], [WhatsApp] o [teléfono]. La solicitud debe incluir nombre e
                                        identificación
                                        del titular, descripción de los hechos, dirección de contacto y los documentos
                                        que la
                                        respalden. Las consultas se responden en máximo diez (10) días hábiles,
                                        prorrogables por
                                        cinco (5) más. Los reclamos se responden en máximo quince (15) días hábiles,
                                        prorrogables por ocho (8) más.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">12. Seguridad</h3>
                                    <p>Cifrado de la información, control de accesos y copias de respaldo.</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">13. Conservación y supresión</h3>
                                    <p>Al terminar la activación, Spoon entrega la base de datos depurada a Eurema y la
                                        borra de
                                        sus sistemas en [plazo acordado por escrito entre las partes]. Eurema conserva
                                        los datos
                                        mientras sea necesario para las finalidades de esta política y la prueba del
                                        consentimiento, por [plazo que defina Eurema].</p>
                                </section>

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">14. Vigencia y cambios</h3>
                                    <p>Esta política rige desde [fecha]. Si cambia la identificación del responsable o
                                        la
                                        finalidad del tratamiento, Eurema lo comunica a los titulares y pide una nueva
                                        autorización cuando el cambio afecte la finalidad.</p>
                                </section>
                            </div>

                            {{-- Pie --}}
                            <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50">
                                <button type="button" @click="policy = false"
                                    class="rounded-full bg-gradient-to-br from-[#14678C] to-[#0A3C55] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#0A3C55]/25 hover:brightness-110 transition">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>
                    <div x-show="promotions" x-cloak
                        x-effect="document.body.classList.toggle('overflow-hidden', promotions)"
                        @keydown.escape.window="promotions = false"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog"
                        aria-modal="true" aria-labelledby="politica-titulo">

                        {{-- Fondo --}}
                        <div x-show="promotions" x-transition.opacity.duration.200ms
                            class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="promotions = false"></div>

                        {{-- Panel --}}
                        <div x-show="promotions" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="relative w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl bg-white shadow-2xl shadow-black/30 overflow-hidden">

                            {{-- Cabecera --}}
                            <div class="flex items-start gap-4 px-6 py-5 bg-gradient-to-br from-[#14678C] to-[#0A3C55]">
                                <div
                                    class="w-11 h-11 shrink-0 rounded-full bg-gradient-to-br from-[#E8C987] to-[#B8913F] flex items-center justify-center shadow-md shadow-black/30">
                                    <i class="fa-solid fa-shield-halved text-[#0A3C55]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 id="politica-titulo"
                                        class="text-lg sm:text-xl font-semibold text-white leading-tight">
                                        Política de Promociones
                                    </h2>
                                </div>
                                <button type="button" @click="promotions = false" title="Cerrar"
                                    class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-white/70 hover:bg-white/10 hover:text-white transition">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Contenido con scroll --}}
                            <div
                                class="flex-1 overflow-y-auto px-6 py-6 text-sm leading-relaxed text-slate-600 space-y-6">

                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-1">1. Responsable</h3>
                                    <p>“Yo, {{ $full_name ?? 'Adulto Responsable' }} , mayor de edad, titular de mis
                                        propios
                                        datos, autorizo de
                                        forma
                                        libre, previa, expresa e informada a [Eurema], responsable del tratamiento, para
                                        que use
                                        mi nombre, mi número de WhatsApp con las siguientes finalidades:</p>
                                </section>
                                <section>
                                    <h3 class="font-semibold text-[#0A3C55] mb-2">Entiendo y acepto lo siguiente:</h3>
                                    <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                        <li> <span class="font-extrabold">Es voluntaria.</span>Puedo no autorizarla. Mi
                                            decisión
                                            no condiciona la compra de la boleta, mi ingreso ni el uso de las
                                            atracciones.</li>
                                        <li> <span class="font-extrabold">Canales.</span> Puedo elegir por cuáles
                                            canales
                                            recibir mensajes: Solo recibiré mensajes por WhatsApp.</li>
                                        <li><span class="font-extrabold">Horarios.</span> Los mensajes publicitarios se
                                            envían
                                            de lunes a viernes entre las 7:00 a.m. y las 7:00 p.m., y los sábados entre
                                            las 8:00
                                            a.m. y las 3:00 p.m. No se envían domingos ni festivos.
                                        </li>
                                        <li><span class="font-bold">Frecuencia.</span> Recibiré como máximo [número]
                                            mensajes
                                            promocionales por [semana o mes].</li>
                                        <li><span class="font-extrabold">Vigencia.</span> Esta autorización rige hasta
                                            que yo la
                                            revoque.</li>
                                        <li><span class="font-extrabold">Datos de menores. </span> Esta autorización no
                                            cubre
                                            los datos del menor que me acompaña, que no se usan para promociones ni
                                            publicidad.
                                        </li>
                                        <li><span class="font-extrabold">Terceros.</span> Mis datos no se venden. Solo
                                            los usa
                                            Eurema y, por su cuenta, Spoon como encargada de la plataforma.</li>
                                        <li><span class="font-extrabold">Puedo revocarla cuando quiera.</span> Mis datos
                                            no se
                                            venden. Solo los usa Eurema y, por su cuenta, Spoon como encargada de la
                                            plataforma.
                                        </li>
                                        <li><span class="font-extrabold">Mis derechos.</span> Puedo conocer, actualizar,
                                            rectificar y pedir la supresión de mis datos, y presentar quejas ante la
                                            Superintendencia de Industria y Comercio, según la Política de Tratamiento
                                            de Datos
                                            Personales de Eurema.</li>
                                        <li><span class="font-extrabold">Prueba.</span> Eurema y Spoon guardan la fecha,
                                            la
                                            hora, mi número de WhatsApp y este texto como evidencia de mi decisión.”
                                        </li>

                                    </ul>
                                </section>
                            </div>

                            {{-- Pie --}}
                            <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50">
                                <button type="button" @click="promotions = false"
                                    class="rounded-full bg-gradient-to-br from-[#14678C] to-[#0A3C55] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#0A3C55]/25 hover:brightness-110 transition">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
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
<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\ParksController;
use App\Traits\traitCruds;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    use traitCruds;
    public $step = 1;

    // Campos Paso 1
    public $sede;
    public $Atraccion;

    //Campos Paso  2
    public $full_name;
    public $type_document;
    public $document_number;
    public $telephone;
    public $email;
    public $check_uno;

    //Campos Paso 3
    public $full_name_minor;
    public $document_type_minor;
    public $document_number_minor;
    public $date;
    public $parentesco;

    //Campos Paso 4
    public $check_dos = false;
    public $check_tres = false;
    public $check_cuatro = false;
    public $check_cinco = false;
    public $check_seis = false;

    public function clear()
    {
        $this->reset(
            'sede',
            'Atraccion',
            'document_number',
            'type_document',
            'full_name',
            'parentesco',
            'telephone',
            'email',
            'document_type_minor',
            'document_number_minor',
            'full_name_minor',
            'date',
            'check_uno',
            'check_dos',
            'check_tres',
            'check_cuatro',
            'check_cinco',
            'check_seis'
        );
        $this->step = 1;
    }
    protected function rules()
    {
        return [
            'sede' => 'required',
            'Atraccion' => 'required|integer',
            'document_number' => 'required|string|min:5|max:20',
            'type_document' => 'required|in:CC,CE,PS',
            'full_name' => 'required|string|min:3|regex:/^([^0-9]*)$/',
            'parentesco' => 'required|string',
            'telephone' => 'required|numeric|digits_between:7,15',
            'email' => 'required|email',

            // Datos del Menor
            'document_number_minor' => 'required|string|min:5|max:20',
            'document_type_minor' => 'required',
            'full_name_minor' => 'required|string|min:3|regex:/^([^0-9]*)$/',
            'date' => 'required|date|before:today' . now()->subYears(2)->format('Y-m-d'),

            // Validar que todos los checks sean obligatorios y aceptados
            'check_uno' => 'accepted',
            'check_dos' => 'accepted',
            'check_tres' => 'accepted',
            'check_cuatro' => 'accepted',
            'check_cinco' => 'accepted',
            'check_seis' => 'accepted',
        ];
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function create()
    {
        try {
            $data = [
                'park_id' => $this->sede,
                'arcade_id' => (int)$this->Atraccion,
                'document_number' => $this->document_number,
                'document_type' => $this->type_document,
                'full_name' => $this->full_name,
                'relationship' => $this->parentesco,
                'phone' => $this->telephone,
                'email' => $this->email,
                'minor_document_number' => $this->document_number_minor,
                'minor_document_type' => $this->document_type_minor,
                'minor_full_name' => $this->full_name_minor,
                'minor_birth_date' => $this->date,
                'check_uno' => $this->check_uno,
                'check_dos' => $this->check_dos,
                'check_tres' => $this->check_tres,
                'check_cuatro' => $this->check_cuatro,
                'check_cinco' => $this->check_cinco,
                'check_seis' => $this->check_seis,
            ];
            $request = new \Illuminate\Http\Request();
            $request->merge($data);
            $this->response = app(ConsetController::class)->store($request);
            if ($this->response['status'] == 'success') {
                if (isset($this->response['data']['url_pdf'])) {
                    $this->showInvoice($this->response['data']['url_pdf']);
                    $this->callNotification('Consentimiento generado', 'success');
                }

                $this->clear();
            } else {

                $this->callNotification($this->response['message'] ?? 'Error en el servidor', 'error');
            }
            $this->endPetition();
        } catch (\Throwable $th) {
            $message = 'Ocurrio un error al enviar el formulario .F';
            $this->handleException($th, $message);
        }
    }
    public function showInvoice($url)
    {
        $filename = basename($url);
        $proxyUrl = url("/pdf-proxy/{$filename}");

        $this->dispatch('open-pdf-popup', url: $proxyUrl);
    }

    public function with()
    {
        $parque = app(ParksController::class)->index();
        $atracciones = app(AtraccionArcadeController::class)->index();
        return [
            'parque' => $parque,
            'atracciones' => $atracciones
        ];
    }
};
?>
<div class="max-w-4xl mx-auto my-10 bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">

    <div x-data="{}" @open-pdf-popup.window="
        const url = $event.detail.url;
        const width = 700;
        const height = 500;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        const features = `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`;

        // Intentar abrir directamente
        const printWindow = window.open(url, '_blank', features);

        if (printWindow) {
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                    setTimeout(() => {
                        if (!printWindow.closed) printWindow.close();
                    }, 15000);
                }, 15000);
            };
        } else {
            alert('El navegador bloqueó la ventana de impresión. Por favor, permite los pop-ups para este sitio.');
        }">
    </div>

    <div class="bg-gray-100 h-2 flex">
        <div class="bg-blue-600 transition-all duration-500" style="width: {{ ($step / 4) * 100 }}%"></div>
    </div>

    <div class="p-8">

        @if($step == 1)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div>
                    <label for="sede" class="block text-sm font-semibold text-gray-700 mb-2">Selecciona una sede</label>
                    <select name="sede" id="sede" wire:model.live="sede"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-all">
                        <option value="">Selecciona una sede</option>
                        @foreach ($parque['data'] as $park)
                        <option value="{{ $park['id'] }}"> {{ $park['name'] }}</option>
                        @endforeach
                    </select>
                    <span>
                        <x-input-error :messages="$errors->get('sede')" />
                    </span>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fa-solid fa-ticket mr-2 text-blue-600"></i> Atracciones Asignadas
                </label>

                <div class="grid grid-cols-1 gap-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse ($atracciones['data'] as $atr)
                    <label
                        class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                        <input type="radio" wire:model="Atraccion" value="{{ $atr['id'] }}"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors">
                            {{ $atr['nombre'] }}
                        </span>
                    </label>
                    @empty
                    <p class="text-xs text-gray-400 italic text-center py-4">No hay atracciones disponibles</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif


        @if($step == 2)
        <div wire:transition class="space-y-4">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 2: Información del Visitante</h2>
            <div class="grid grid-cols-1 gap-4">
                <input type="text" wire:model="full_name" placeholder="Nombre completo"
                    @input="$el.value = $el.value.replace(/[0-9]/g, '')" class="p-3 border rounded-lg">
                <select class="p-3 border rounded-lg" wire:model="type_document">
                    <option value="">Escoja un tipo de documento</option>
                    <option value="CC">Cédula</option>
                    <option value="CE">Cédula Extranjera</option>
                    <option value="PS">Pasaporte</option>
                </select>
                <input wire:model="document_number" type="tel" placeholder="Número de Documento"
                    class="p-3 border rounded-lg">
                <input wire:model="telephone" type="tel" placeholder="Número de Teléfono" class="p-3 border rounded-lg">
                <input type="email" wire:model="email" placeholder="Correo electrónico" class="p-3 border rounded-lg">

                <p class="text-sm text-gray-600 leading-relaxed italic">
                    Declaro bajo la gravedad de juramento que soy padre, madre o acudiente legal del menor identificado
                    en este formato y que cuento con facultades vigentes para autorizar su participación en la atracción
                    descrita.
                </p>

                <label
                    class="flex text-md items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input type="checkbox" wire:model="check_uno"
                        class="w-8 h-7 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors">
                        Confirmo mi calidad de representante legal del menor.
                    </span>
                </label>
            </div>
        </div>
        @endif


        @if($step == 3)
        <div wire:transition class="space-y-4">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 3: Información del Menor</h2>
            <div class="grid grid-cols-1 gap-4">
                <input type="text" @input="$el.value = $el.value.replace(/[0-9]/g, '')" wire:model="full_name_minor"
                    placeholder="Nombre completo del menor" class="p-3 border rounded-lg">
                <select class="p-3 border rounded-lg" wire:model="document_type_minor">
                    <option value="">Elija Tipo de documento</option>
                    <option value="RC">Registro Civil</option>
                    <option value="TI">Tarjeta de Identidad</option>
                </select>
                <input type="text" wire:model="document_number_minor" placeholder="Numero de Documento"
                    class="p-3 border rounded-lg">
                <label max="{{ date('Y-m-d') }}" for="date">Año de Nacimiento</label>
                <input type="date" wire:model="date" class="p-3 border rounded-lg">
                <select class="p-3 border rounded-lg" wire:model="parentesco">
                    <option value="">Elija un parentesco</option>
                    <option value="Padre">Padre</option>
                    <option value="Madre">Madre</option>
                    <option value="Acudiente">Acudiente</option>
                </select>
            </div>
        </div>
        @endif

        @if($step == 4)
        <div wire:transition class="space-y-4">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 4: Declaraciones y Consentimiento</h2>

            <div class="space-y-3">
                <label
                    class="flex items-start p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input wire:model="check_dos" type="checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700 leading-tight">Declaro que el menor cumple con los
                        requisitos de estatura, edad y condiciones físicas exigidas para el
                        uso de la atracción seleccionada.</span>
                </label>
                <label
                    class="flex items-start p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input wire:model="check_tres" type="checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700 leading-tight">Declaro bajo la gravedad de juramento que el
                        menor NO presenta condiciones médicas, físicas o de salud
                        que puedan agravarse, descompensarse o generar riesgo con el uso de esta atracción.</span>
                </label>
                <label
                    class="flex items-start p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input wire:model="check_cuatro" type="checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700 leading-tight">Confirmo haber leído y comprendido el
                        reglamento visible en la atracción, las restricciones de ingreso
                        y las advertencias de seguridad.</span>
                </label>
                <label
                    class="flex items-start p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input wire:model="check_cinco" type="checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700 leading-tight">Declaro que me comprometo a ejercer
                        supervisión permanente y activa del menor mientras utilice la atracción.</span>
                </label>

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500 leading-relaxed mb-3">
                        Entiendo y acepto que STARK PARK no será responsable por incidentes, lesiones o daños que se
                        originen por incumplimiento de las normas de seguridad o instrucciones del operador.
                    </p>
                    <label class="flex items-start cursor-pointer group">
                        <input wire:model="check_seis" type="checkbox"
                            class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded" x-on:change="
                            if($el.checked) {
                            $wire.check_uno = true;
                            $wire.check_dos = true;
                            $wire.check_tres = true;
                            $wire.check_cuatro = true;
                            $wire.check_cinco = true;
                            }else{
                            $wire.check_uno = false;
                            $wire.check_dos = false;
                            $wire.check_tres = false;
                            $wire.check_cuatro = false;
                            $wire.check_cinco = false;
                                }">
                        <span class="ml-3 text-xs font-semibold text-gray-700">Acepto integralmente el consentimiento
                            informado y exoneración de responsabilidad.</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-10 flex justify-between border-t pt-6">
            @if($step > 1)
            <button type="button" wire:click="previousStep"
                class="text-gray-600 font-bold py-2 px-6 rounded-lg hover:bg-gray-100 transition-all">
                Atrás
            </button>
            @else
            <div></div>
            @endif

            @if($step < 4) <button type="button" wire:click="nextStep"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-lg shadow-md transition-all"
                :disabled="($wire.step == 1 && (!$wire.sede || !$wire.Atraccion)) 
                || ($wire.step == 2 && (!$wire.full_name || !$wire.type_document || !$wire.document_number || !$wire.telephone || !$wire.email || !$wire.check_uno)) ||
                ($wire.step == 3 && (!$wire.full_name_minor || !$wire.document_type_minor || !$wire.document_number_minor || !$wire.date || !$wire.parentesco))
                "
                :class=" {'bg-gray-600 opacity-25  cursor-not-allowed': ($wire.step == 1 && (!$wire.sede || !$wire.Atraccion)) || ($wire.step == 2 && (!$wire.full_name || !$wire.type_document || !$wire.document_number || !$wire.telephone || !$wire.email || !$wire.check_uno))
                ||($wire.step == 3 && (!$wire.full_name_minor || !$wire.document_type_minor || !$wire.document_number_minor || !$wire.date || !$wire.parentesco))}">
                Siguiente
                </button>
                @else
                <button @click="window.dispatchEvent(new CustomEvent('show-loading', {
                                                    detail: {
                                                        message : 'Cargando ... '
                                                    }
                                                }))" type="button" wire:click="create"
                    :disabled="!$wire.check_dos || !$wire.check_tres || !$wire.check_cuatro || !$wire.check_cinco || !$wire.check_seis"
                    class="text-white font-bold py-2 px-8 rounded-lg shadow-md transition-all" :class="{

                            'bg-green-200 cursor-not-allowed': !$wire.check_dos,
                            'bg-green-300 cursor-not-allowed': $wire.check_dos && !$wire.check_tres,
                            'bg-green-400 cursor-not-allowed': $wire.check_tres && !$wire.check_cuatro,
                            'bg-green-500 cursor-not-allowed': $wire.check_cuatro && !$wire.check_cinco,
                            
                            'bg-green-600 hover:bg-green-700 cursor-pointer animate-pulse': $wire.check_dos && $wire.check_tres && $wire.check_cuatro && $wire.check_cinco && $wire.check_seis
                        }">

                    Finalizar Registro
                </button>
                @endif
        </div>
    </div>
</div>
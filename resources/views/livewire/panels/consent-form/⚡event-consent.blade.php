<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\ParksController;
use App\Traits\traitCruds;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Aws\S3\S3Client;

new #[Layout('layouts.guest')] class extends Component
{
    use traitCruds, WithFileUploads;
    public $step = 1;

    // Campos Paso 1
    public $sede;
    public $Atraccion;
    public $check_siete;

    //Campos Paso  2
    public $full_name;
    public $type_document;
    public $document_number;
    public $telephone;
    public $email;
    public $check_uno;

    //Campos Paso 3
    public $date;
    public $event_file;

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
            'telephone',
            'email',
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
            'type_document' => 'required|in:CC,NIT,CE,PS',
            'full_name' => 'required|string|min:3|regex:/^([^0-9]*)$/',
            'telephone' => 'required|numeric|digits_between:7,15',
            'email' => 'required|email',
            'event_file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg,webp|max:2048',

            // Datos del Menor

            'date' => 'required|date|after_or_equal:today',

            // Validar que todos los checks sean obligatorios y aceptados
            'check_uno' => 'accepted',
            'check_dos' => 'accepted',
            'check_tres' => 'accepted',
            'check_cuatro' => 'accepted',
            'check_cinco' => 'accepted',
            'check_seis' => 'accepted',
        ];
    }

    public function create()
    {
        try {
            $finalUrl = null;
            if ($this->event_file && !is_string($this->event_file)) {
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => env('AWS_DEFAULT_REGION', 'us-east-2'),
                    'credentials' => [
                        'key' => env('AWS_ACCESS_KEY_ID'),
                        'secret' => env('AWS_SECRET_ACCESS_KEY'),
                    ],

                ]);


                $bucket = env('AWS_BUCKET');
                $fileName = time() . '_' . $this->event_file->getClientOriginalName();
                $key = 'legal/pdf/' . $fileName;

                $result = $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => file_get_contents($this->event_file->getRealPath()),
                    'ContentType' => $this->event_file->getMimeType(),
                ]);

                $finalUrl = $result['ObjectURL'];
            } else {

                $finalUrl = $this->event_file;
            }
            $data = [
                'park_id' => $this->sede,
                'arcade_id' => (int)$this->Atraccion,
                'document_number' => $this->document_number,
                'document_type' => $this->type_document,
                'full_name' => $this->full_name,
                'phone' => $this->telephone,
                'email' => $this->email,
                'url_file' => $finalUrl,
                'event_date' => $this->date,
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

        $this->dispatch('open-pdf-popup', url: $url);
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
    <div class="h-2 bg-blue-600 transition-all duration-500 z-1" :style="`width: ${($wire.step / 4) * 100}%`">
    </div>
    <div
        class="flex flex-col md:flex-row justify-center md:justify-between items-center p-6 gap-8 w-full max-w-4xl mx-auto">
        <div class="w-full md:w-auto flex justify-center">
            <img src="images/logohori.png" alt="Logo de Star Park" class="h-10 md:h-14 w-auto object-contain">
        </div>

        <div class="w-full md:w-auto flex justify-center">
            <img src="images/modo-karting.png" alt="Logo de Modo Karting" class="h-10 md:h-24 w-auto object-contain">
        </div>
    </div>
    <div class="md:fixed md:right-4 md:bottom-4 absolute bottom-[-3rem] right-[11.5rem] opacity-50 z-50">
        <img class="md:w-[70px] w-[60px]" src="images\spoon-trasp.png" alt="logo de Spoon">
    </div>
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


    <div class="p-8" x-data="{ 

                    get step() { 
                        return this.$wire.step 
                    },
                    
                    get canGoNext() {
                        if (this.step == 1) return this.$wire.sede && this.$wire.Atraccion && this.$wire.check_siete;
                        if (this.step == 2) return this.$wire.full_name && this.$wire.type_document && this.$wire.document_number && this.$wire.telephone && this.$wire.email && this.$wire.check_uno;
                        if (this.step == 3) return this.$wire.date && this.$wire.event_file;
                        if (this.step == 4) return this.$wire.check_tres && this.$wire.check_cuatro && this.$wire.check_cinco && this.$wire.check_seis;
                        return true;
                    },

                    next() { 
                        if(this.canGoNext) {
                            this.$wire.step++; 
                        }
                    },
                    
                    back() { 
                        if(this.step > 1) {
                            this.$wire.step--;
                        }
                    }
                }">

        <div x-show="step == 1"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="space-y-2">
                    <label for="sede" class="block text-sm font-semibold text-gray-700">Selecciona una sede</label>
                    <select name="sede" id="sede" wire:model.live="sede"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-all">
                        <option value="">Selecciona una sede</option>
                        @foreach ($parque['data'] as $park)
                        <option value="{{ $park['id'] }}"> {{ $park['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('sede')" />
                </div>

                <div class="space-y-2">

                    <label for="atracciones" class="text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fa-solid fa-ticket mr-2 text-blue-600"></i> Atracciones Asignadas
                    </label>
                    <select wire:model.live="Atraccion" id="atracciones" name="atracciones"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-all cursor-pointer hover:bg-white">
                        <option value="">Seleccione una atracción</option>
                        @foreach ($atracciones['data'] as $atr)
                        <option value="{{ $atr['id'] ?? $atr->id }}">{{ $atr['nombre'] ?? $atr->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('Atraccion')" />
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="flex items-start md:items-center space-x-3">
                    <div class="flex-shrink-0">
                        <input type="checkbox" wire:model.live="check_siete"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    </div>

                    <div class="text-xs text-gray-500 leading-normal">
                        <p class="inline">
                            Acepto y declaro haber leído y comprendido la
                            <span class="font-semibold text-gray-700">Política de Tratamiento de Datos Personales</span>
                            y Condiciones de Uso de Star Park.
                        </p>
                        <a href="https://www.starpark.com.co/_files/ugd/3d37ac_dcce5a8525d140baaa959eca88cfe1e2.pdf"
                            target="_blank"
                            class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center transition-colors ml-1 font-medium">
                            <i class="fa-solid fa-file-pdf mx-1"></i>
                            Ver Política de Privacidad
                        </a>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('check_uno')" class="mt-2" />
            </div>
        </div>



        <div x-show="$wire.step == 2" x-cloak
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-4">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 2: Información del Visitante</h2>
            <div class="grid grid-cols-1 gap-4">
                <input type="text" wire:model="full_name" placeholder="Nombre completo"
                    @input="$el.value = $el.value.replace(/[0-9]/g, '')" class="p-3 border rounded-lg">
                <select class="p-3 border rounded-lg" wire:model="type_document">
                    <option value="">Escoja un tipo de documento</option>
                    <option value="CC">Cédula</option>
                    <option value="CE">Cédula Extranjera</option>
                    <option value="NIT">NIT</option>
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




        <div x-show="$wire.step == 3" x-cloak
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            wire:transition class="space-y-4">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 3: Información del Evento</h2>
            <div class="grid grid-cols-1 gap-4">

                <label for="date">Fecha del evento</label>
                <input type="date" wire:model.live="date" min="{{ date('Y-m-d') }}" class="p-3 border rounded-lg">
                <x-input-error :messages="$errors->get('date')" />
                <label max="{{ date('Y-m-d') }}" for="date">Archivo con los niños que van a asistir</label>
                <input type="file" wire:model="event_file" class="p-3 border rounded-lg">
                <div wire:loading wire:target="event_file" class="text-sm text-blue-500">
                    Subiendo archivo...
                </div>

            </div>
        </div>


        <div x-show="$wire.step == 4" x-cloak
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-4">
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


        <div class="mt-10 flex justify-between border-t pt-6">

            <div class="flex-1" x-cloak>
                <button x-show="$wire.step > 1" type="button" @click="back()"
                    class="text-gray-600 font-bold py-2 px-6 rounded-lg hover:bg-gray-100 transition-all">
                    Atrás
                </button>
            </div>

            <template x-if="$wire.step < 4">
                <button type="button" @click="next()" :disabled="!canGoNext"
                    class="font-bold py-2 px-8 rounded-lg shadow-md transition-all text-white"
                    :class="canGoNext ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 opacity-50 cursor-not-allowed'">
                    Siguiente
                </button>
            </template>

            <template x-if="$wire.step == 4">
                <button type="button" @click="
                    if(canGoNext) {
                        window.dispatchEvent(new CustomEvent('show-loading', { detail: { message: 'Cargando...' } }));
                        $wire.create();
                    }
                " :disabled="!canGoNext" class="text-white font-bold py-2 px-8 rounded-lg shadow-md transition-all"
                    :class="{
                    'bg-green-200 cursor-not-allowed': !$wire.check_dos,
                    'bg-green-300 cursor-not-allowed': $wire.check_dos && !$wire.check_tres,
                    'bg-green-400 cursor-not-allowed': $wire.check_tres && !$wire.check_cuatro,
                    'bg-green-500 cursor-not-allowed': $wire.check_cuatro && !$wire.check_cinco,
                    'bg-green-600 hover:bg-green-700 cursor-pointer animate-pulse': canGoNext
                }">
                    Finalizar Registro
                </button>
            </template>
        </div>
    </div>
</div>
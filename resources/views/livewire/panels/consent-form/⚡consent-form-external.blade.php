<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Http\Controllers\Operation\ClientController;
use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\ParksController;
use App\Models\Operation\AtraccionArcade;
use App\Traits\traitCruds;
use AWS\CRT\HTTP\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    use traitCruds;
    public $step = 1;
    public $client;
    // Campos Paso 1
    public $sede = 13;
    public $Atraccion = 12;
    public $check_siete;

    //Campos Paso  2
    public $full_name;
    public $type_document;
    public $document_number;
    public $telephone;
    public $email;
    public $check_uno;
    public $income;

    //Campos Paso 3
    public $full_name_minor;
    public $childrens = [];
    public $date;
    public $parentesco;

    //Campos Paso 4
    public $check_dos = false;
    public $check_tres = false;
    public $check_cuatro = false;
    public $check_cinco = false;
    public $check_seis = false;
    public $clientFound;

    public function consult()
    {
        $this->validate(['client' => 'required|digits_between:7,15'], [
            'client.required' => 'Ingresa un número de teléfono.',
            'client.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
        ]);

        $res = app(ClientController::class)->indexPaginated(1, 1, $this->client);
        $cliente = collect($res['data']['data'] ?? [])->first();

        if (! $cliente) {
            $this->clientFound = false;
            return;
        }

        $this->clientFound     = true;
        $this->full_name       = trim(data_get($cliente, 'first_name') . ' ' . data_get($cliente, 'last_name'));
        $this->email           = data_get($cliente, 'email');
        $this->document_number = data_get($cliente, 'document_number');
        $this->type_document   = data_get($cliente, 'document_type');
        $this->telephone       = data_get($cliente, 'phone');
    }

    public function clearConsult()
    {
        $this->reset('client', 'clientFound', 'full_name', 'email', 'document_number', 'type_document', 'telephone');
        $this->resetValidation('client');
    }
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
            'full_name_minor',
            'date',
            'check_uno',
            'check_dos',
            'check_tres',
            'check_cuatro',
            'check_cinco',
            'check_seis',
            'check_siete',
            'income'
        );
        $this->step = 1;
        $this->childrens = [];
        $this->dispatch('resetDataAlpine');
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
            'full_name_minor' => 'required|string|min:3|regex:/^([^0-9]*)$/',
            'date' => 'required|date|before:today' . now()->subYears(2)->format('Y-m-d'),

            // Validar que todos los checks sean obligatorios y aceptados
            'check_uno' => 'nullable',
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
            if ($this->income == true) {
                $this->income = 1;
                $data = [
                    'park_id' => $this->sede,
                    'arcade_id' => (int)$this->Atraccion,
                    'document_number' => $this->document_number,
                    'document_type' => $this->type_document,
                    'full_name' => $this->full_name,
                    'relationship' => $this->parentesco,
                    'phone' => $this->telephone,
                    'email' => $this->email,
                    'unique_person' => $this->income,
                    'check_uno' => $this->check_uno,
                    'check_dos' => $this->check_dos,
                    'check_tres' => $this->check_tres,
                    'check_cuatro' => $this->check_cuatro,
                    'check_cinco' => $this->check_cinco,
                    'check_seis' => $this->check_seis,
                ];
            } else {
                $this->income = 0;
                $data = [
                    'park_id' => $this->sede,
                    'arcade_id' => (int)$this->Atraccion,
                    'document_number' => $this->document_number,
                    'document_type' => $this->type_document,
                    'full_name' => $this->full_name,
                    'relationship' => $this->parentesco,
                    'phone' => $this->telephone,
                    'email' => $this->email,
                    'childrens' => $this->childrens,
                    'unique_person' => $this->income,
                    'check_uno' => $this->check_uno,
                    'check_dos' => $this->check_dos,
                    'check_tres' => $this->check_tres,
                    'check_cuatro' => $this->check_cuatro,
                    'check_cinco' => $this->check_cinco,
                    'check_seis' => $this->check_seis,
                ];
            }
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
        $parque = app(ParksController::class)->index(2);
        $atracciones = AtraccionArcade::select('id', 'nombre')->where('brand_id', 2)->get()->toArray();
        return [
            'parque' => $parque,
            'atracciones' => $atracciones
        ];
    }
};
?>
<div class="max-w-4xl mx-auto my-10 bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100" x-data="{income:@entangle('income').live,
    atracction:@entangle('Atraccion').live}">
    <div class="h-2 bg-blue-600 transition-all duration-500 z-1" :style="`width: ${($wire.step / 4) * 100}%`">
    </div>
    <div
        class="flex flex-row md:flex-row justify-center md:justify-between items-center p-6 gap-8 w-full max-w-4xl mx-auto">
        <div class="w-full md:w-auto flex justify-center">
            <img src="https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1793039364_LOGOS%20EUREMA_Mesa%20de%20trabajo%201%20copia%2016.png"
                alt="Logo de Eurema"
                class="h-14 md:h-20 w-auto object-contain {{ $sede == 13 ? 'flex justify-center items-center text-center' : '' }}">
        </div>

        <div class="w-full md:w-auto flex justify-center">
            <img src="https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1793043639_Mosntropolis-blanco.png"
                alt="Logo monstropolis" class="h-14 md:h-24 w-auto object-contain">
        </div>
    </div>
    <div class="md:fixed md:right-4 md:bottom-4 absolute bottom-[-3rem] right-[11.5rem] opacity-50 z-50">
        <img class="md:w-[70px] w-[60px]" src="/images/spoon-trasp.png" alt="logo de Spoon">
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
                    saving: false,
                    openConsent: false,
                    policy: false,
                    promotions: false,
                    items: [],
                     async confirmarConsentimiento() {
                        if (this.saving) return;
                        this.saving = true;
                        try {
                            await this.guardar('create');
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.saving = false;
                            this.openConsent = false;
                        }
                    },
                    get step() { 
                        return this.$wire.step 
                    },
                    
                    get canGoNext() {
                        if (this.step == 1) return this.$wire.sede && this.$wire.Atraccion;
                        if (this.step == 2) return this.$wire.full_name && this.$wire.type_document && this.$wire.document_number && this.$wire.telephone && this.$wire.email && this.$wire.check_uno || this.$wire.income;
                        if (this.step == 3) return this.items.length > 0 && this.items.every(item => item.minor_full_name && item.minor_birth_date) && (this.$wire.Atraccion == 10 || !!this.$wire.parentesco);; 
                        if (this.step == 4) return this.$wire.check_tres && this.$wire.check_cuatro && this.$wire.check_cinco && this.$wire.check_seis;
                        return true;
                    },

                   next() { 
                        if (income && this.$wire.step == 2 ) {
                            this.$wire.step = 4;
                        } else if (this.canGoNext) {
                            this.$wire.step++; 
                        }
                    },
                    
                    back() { 
                        if(this.step == 4 && income == true){
                            this.$wire.step = 2;
                        }
                        else if(this.step > 1) {
                            this.$wire.step--;
                        }
                    },
                resetDataAlpine(){
                this.items.length = 0;
                },
                addChildren() {
                    this.items.push({
                        minor_full_name: '',
                        minor_birth_date: '' ,
                     });
                    },
                    loadChildren(items){
                      if (items.children_details.length > 0) {
                                items.children_details.forEach(item => {
                                    this.items.push({
                                        minor_full_name: item.minor_full_name,
                                        minor_birth_date: item.minor_birth_date,
                                    });
                                });
                    }
                    },
                    async prepararDetallesEvento() {
                            await Promise.all([
                                this.$wire.set('childrens', this.items)
                            ]);
                        },
                    async guardar(livewireMethod) {
                            await this.prepararDetallesEvento();
                            await this.$nextTick();
                            await this.$wire[livewireMethod]();
                            this.resetDataAlpine();
                        },
                }" @reset-data-alpine.window="resetDataAlpine()">

        <div x-show=" step==1"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-8">
            <div
                class="relative w-full overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F2A44] via-[#155C7C] to-[#224B9A] p-6 shadow-lg">

                <div
                    class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-[#E8C987]/20 blur-3xl">
                </div>

                <div class="relative flex flex-col items-center gap-4 text-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Consultar cliente</h2>
                        <p class="mt-1 text-sm text-white/70">
                            <i class="fa-solid fa-circle-info mr-1 text-[#E8C987]"></i>
                            Úsa este buscador solo si ya estás registrado en Eurema
                        </p>
                    </div>

                    <form wire:submit="consult"
                        class="flex w-full max-w-lg items-center rounded-full border border-white/15 bg-white/10 p-1.5 shadow-lg shadow-black/20 backdrop-blur-sm transition focus-within:border-[#E8C987]/50 focus-within:bg-white/15 focus-within:ring-4 focus-within:ring-[#E8C987]/10">
                        <i class="fa-solid fa-phone ml-3 text-sm text-[#E8C987]"></i>

                        <input type="tel" inputmode="numeric" maxlength="15" wire:model="client"
                            @input="$el.value = $el.value.replace(/\D/g, '')" placeholder="Número de teléfono"
                            aria-label="Número de teléfono"
                            class="min-w-0 flex-1 border-0 bg-transparent px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:ring-0">

                        @if ($client)
                        <button type="button" wire:click="clearConsult" title="Limpiar"
                            class="mr-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-white/50 transition hover:bg-white/10 hover:text-white">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                        @endif

                        <button type="submit" wire:loading.attr="disabled" wire:target="consult"
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-br from-[#E8C987] to-[#B8913F] px-5 py-2 text-sm font-semibold text-[#0A3C55] shadow-md shadow-black/30 transition hover:brightness-110 active:scale-[0.98] disabled:opacity-70">
                            <span wire:loading.remove wire:target="consult">Buscar</span>
                            <i wire:loading wire:target="consult" class="fa-solid fa-circle-notch fa-spin"></i>
                        </button>
                    </form>

                    {{-- Resultado --}}
                    @if ($clientFound === true)
                    <p
                        class="flex items-center gap-2 rounded-full bg-emerald-500/15 px-4 py-1.5 text-xs font-medium text-emerald-200">
                        <i class="fa-solid fa-circle-check"></i>
                        Cliente encontrado, cargamos tus datos en el paso 2
                    </p>
                    @elseif ($clientFound === false)
                    <p
                        class="flex items-center gap-2 rounded-full bg-red-500/15 px-4 py-1.5 text-xs font-medium text-red-200">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        No encontramos ese número. Puedes registrarte en el paso 2
                    </p>
                    @endif
                </div>
            </div>
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
                        @foreach ($atracciones as $atr)
                        <option value="{{ $atr['id'] ?? $atr->id }}">{{ $atr['nombre'] ?? $atr->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('Atraccion')" />
                </div>
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
                <label
                    class="flex text-md items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-colors cursor-pointer group">
                    <input type="checkbox" wire:model="income"
                        class="w-8 h-7 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors">
                        Ingreso Solo.
                    </span>
                </label>
            </div>
        </div>




        <div x-show="$wire.step == 3" x-cloak x-transition:enter="transition ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-4">

            <h2 class="text-xl font-bold mb-6 text-gray-800">Paso 3: Información del Visitante</h2>
            <div class="flex justify-between">
                <button type="button" @click="addChildren()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg  transition-colors"
                    :class="items.length >= 5  ? 'bg-gray-300 text-black cursor-not-allowed hover:bg-none' : 'bg-blue-600'"
                    :disabled="items.length >= 5">
                    <span x-text="atracction !=10 ? 'Agregar Menor' : 'Agregar Visitante'"> </span>
                </button>
                <select x-show="atracction != 10 " class="p-3 border rounded-lg bg-white" wire:model="parentesco">
                    <option value=""> Parentesco</option>
                    <option value="Padre">Padre</option>
                    <option value="Madre">Madre</option>
                    <option value="Acudiente">Acudiente</option>
                </select>
            </div>
            <template x-for="(item, index) in items" :key="index">
                <div class="flex flex-col gap-4 p-4 border rounded-xl bg-gray-50 relative mt-4">

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-600">Datos del Visitante #<span
                                x-text="index + 1"></span></span>

                        <button type="button" @click="items.splice(index, 1)"
                            class="text-red-500 hover:text-red-700 hover:bg-red-100 p-2 rounded-full transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <input type="text" @input="$el.value = $el.value.replace(/[0-9]/g, '')"
                        x-model="item.minor_full_name" placeholder="Nombre completo del Visitante"
                        class="p-3 border rounded-lg bg-white">

                    <label class="block text-sm font-medium text-gray-700 -mb-2">Fecha de Nacimiento</label>
                    <input min="{{ date('Y-m-d', strtotime('-80 year')) }}"
                        max="{{ date('Y-m-d' , strtotime('-6 months')) }}" type="date" x-model="item.minor_birth_date"
                        class="p-3 border rounded-lg bg-white">
                </div>
            </template>
            <div x-show="items.length >= 5" x-transition
                class="flex p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 border border-blue-200 mt-4"
                role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    <span class="font-semibold">Límite de Visitantes alcanzado:</span> Este formulario permite un máximo
                    de 5 Visitantes por inscripción. Si necesitas añadir más, por favor completa el registro actual y
                    genera uno
                    nuevo.
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
                        Entiendo y acepto que Eurema no será responsable por incidentes, lesiones o daños que se
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
                    if(canGoNext) { openConsent = true; }
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
        <div x-show="openConsent" x-cloak x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">

            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="if (!saving) openConsent = false">
            </div>

            <div
                class="relative flex flex-col items-center gap-4 rounded-2xl bg-white px-8 py-7 shadow-2xl shadow-black/30 w-full max-w-sm">

                {{-- Confirmación --}}
                <div x-show="!saving" class="flex flex-col items-center gap-4 text-center">
                    <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center">
                        <i class="fa-solid fa-file-signature text-xl text-blue-700"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-800">!! IMPORTANTE ¡¡</p>
                        <p class="mt-1 text-md text-red-700">Guarda este consentimiento durante tu estadía en el
                            parque.</p>
                    </div>
                    <div class="flex w-full gap-2">
                        <button type="button" @click="openConsent = false"
                            class="flex-1 rounded-xl border border-slate-300 p-2 text-sm text-slate-700 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmarConsentimiento()" :disabled="saving"
                            class="flex-1 rounded-xl bg-blue-700 p-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                            Aceptar
                        </button>
                    </div>
                </div>

                {{-- Cargando --}}
                <div x-show="saving" class="flex flex-col items-center gap-4 text-center">
                    <div class="relative h-14 w-14">
                        <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>
                        <div
                            class="absolute inset-0 rounded-full border-4 border-transparent border-t-[#FD7A0F] animate-spin">
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Generando consentimiento...</p>
                        <p class="mt-1 text-xs text-slate-500">Por favor espera un momento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
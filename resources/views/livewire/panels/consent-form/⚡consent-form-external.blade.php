<?php

use App\Http\Controllers\Operation\AtraccionArcadeController;
use App\Http\Controllers\Operation\ClientController;
use App\Http\Controllers\Operation\ConsetController;
use App\Http\Controllers\Operation\ParksController;
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

    public function consult()
    {
        $user = app(ClientController::class)->indexPaginated(1, 1, $this->client);
        $this->full_name = $user['data']['first_name'] ?? null;
        $this->email = $user['data']['email'] ?? null;
        $this->document_number = $user['data']['document_number'] ?? null;
        $this->type_document = $user['data']['document_type'] ?? null;
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
        $parque = app(ParksController::class)->index();
        $atracciones = app(AtraccionArcadeController::class)->index();
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
        class="flex flex-col md:flex-row justify-center md:justify-between items-center p-6 gap-8 w-full max-w-4xl mx-auto">
        <div class="w-full md:w-auto flex justify-center">
            <img src="" alt="Logo de Eurema"
                class="h-10 md:h-14 w-auto object-contain {{ $sede == 13 ? 'flex justify-center items-center text-center' : '' }}">
        </div>

        <div class="w-full md:w-auto flex justify-center">
            <img src="" alt="Otro Logo" class="h-10 md:h-24 w-auto object-contain">
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
                    policy: false,
                    promotions: false,
                    items: [], 
                    get step() { 
                        return this.$wire.step 
                    },
                    
                    get canGoNext() {
                        if (this.step == 1) return this.$wire.sede && this.$wire.Atraccion && this.$wire.check_siete;
                        if (this.step == 2) return this.$wire.full_name && this.$wire.type_document && this.$wire.document_number && this.$wire.telephone && this.$wire.email && this.$wire.check_uno;
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
            <div class="flex w-full ">
                <div
                    class="space-x-10 w-full relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F2A44] via-[#155C7C] to-[#224B9A] p-6 shadow-lg">
                    <h2 class="relative m-2 text-2xl font-bold text-white">Consultar Cliente</h2>

                    <form wire:submit.prevent="consult"
                        class="flex flex-1 w-full max-w-lg items-center rounded-full bg-white/10 backdrop-blur-sm border border-white/15 p-1.5 shadow-lg shadow-black/20 transition focus-within:bg-white/15 focus-within:border-[#E8C987]/50 focus-within:ring-4 focus-within:ring-[#E8C987]/10">
                        <i class="fa-solid fa-magnifying-glass ml-3 text-sm text-[#E8C987]"></i>
                        <input type="text" wire:model="client" placeholder="Teléfono"
                            class="flex-1 min-w-0 bg-transparent border-0 px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:ring-0">

                        {{-- Limpiar (solo si hay texto) --}}
                        @if ($search)
                        <button type="button" title="Limpiar"
                            class="mr-1 w-7 h-7 shrink-0 rounded-full flex items-center justify-center text-white/50 hover:bg-white/10 hover:text-white transition">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                        @endif

                        <button type="submit" wire:loading.attr="disabled" wire:target="buscar"
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-br from-[#E8C987] to-[#B8913F] px-5 py-2 text-sm font-semibold text-[#0A3C55] shadow-md shadow-black/30 hover:brightness-110 active:scale-[0.98] disabled:opacity-70 transition">
                            <span wire:loading.remove wire:target="buscar">Buscar</span>
                            <i wire:loading wire:target="buscar" class="fa-solid fa-circle-notch fa-spin"></i>
                        </button>
                    </form>
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
                            y Condiciones de Uso de Eurema.
                        </p>
                        <button @click="policy = true"
                            class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center transition-colors ml-1 font-medium">
                            <i class="fa-solid fa-file-pdf mx-1"></i>
                            Ver Política de Privacidad
                        </button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('check_uno')" class="mt-2" />

                <div class="flex items-start md:items-center space-x-3">
                    <div class="flex-shrink-0">
                        <input type="checkbox" wire:model.live="check_siete"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    </div>

                    <div class="text-xs text-gray-500 leading-normal">
                        <p class="inline">
                            Sí, autorizo que me envíen promociones e invitaciones de Eurema
                            <span class="font-semibold text-gray-700">Autorización para el uso de datos en el envío de
                                promociones
                            </span>
                        </p>
                        <button @click="promotions = true"
                            class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center transition-colors ml-1 font-medium">
                            <i class="fa-solid fa-file-pdf mx-1"></i>
                            Ver Política de Promociones
                        </button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('check_uno')" class="mt-2" />
            </div>
            <div x-show="policy" x-cloak x-effect="document.body.classList.toggle('overflow-hidden', policy)"
                @keydown.escape.window="policy = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true"
                aria-labelledby="politica-titulo">

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
                            <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-[#E8C987]">Ley 1581 de
                                2012</p>
                            <h2 id="politica-titulo" class="text-lg sm:text-xl font-semibold text-white leading-tight">
                                Política de tratamiento de datos personales
                            </h2>
                        </div>
                        <button type="button" @click="policy = false" title="Cerrar"
                            class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-white/70 hover:bg-white/10 hover:text-white transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Contenido con scroll --}}
                    <div class="flex-1 overflow-y-auto px-6 py-6 text-sm leading-relaxed text-slate-600 space-y-6">

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">1. Responsable</h3>
                            <p>[Razón social de Eurema], NIT [por completar], domicilio [dirección y ciudad], correo
                                [por completar], teléfono [por completar].</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">2. Encargado</h3>
                            <p>Spoon, NIT [por completar], proveedor de la plataforma NoCard by NEXO, que registra al
                                visitante y conserva la evidencia de su aceptación. Spoon trata los datos solo por
                                cuenta de Eurema y según sus instrucciones.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">3. Marco legal</h3>
                            <p>Artículos 15 y 20 de la Constitución Política, Ley Estatutaria 1581 de 2012, Decreto 1074
                                de 2015 (que compila el Decreto 1377 de 2013) y demás normas que los modifiquen.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-2">4. Datos que se recolectan</h3>
                            <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                <li><span class="font-medium text-slate-700">Del adulto responsable:</span> nombre,
                                    documento de identidad, número de WhatsApp y, si lo entrega, correo electrónico.
                                </li>
                                <li><span class="font-medium text-slate-700">Del menor:</span> nombre, edad y estatura,
                                    para verificar los requisitos de la atracción, y la atracción que va a usar.</li>
                                <li>Declaración sobre las condiciones médicas, físicas o de salud del menor. Es un dato
                                    sensible (ver numeral 7).</li>
                                <li>Evidencia de cada aceptación: fecha, hora, número de WhatsApp y texto aceptado.</li>
                            </ul>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-2">5. Finalidades</h3>
                            <p class="font-medium text-slate-700 mb-1">Finalidades necesarias para el servicio:</p>
                            <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                <li>Registrar al visitante y al menor y verificar los requisitos de acceso a cada
                                    atracción.</li>
                                <li>Conservar la prueba del consentimiento informado.</li>
                                <li>Atender consultas, incidentes y solicitudes durante la activación, por WhatsApp o
                                    con los asesores de Eurema.</li>
                                <li>Proteger la seguridad de los visitantes y atender requerimientos de autoridades
                                    competentes.</li>
                                <li>Elaborar estadísticas de asistencia de forma agregada, sin identificar a la persona.
                                </li>
                            </ul>
                            <div class="mt-3 rounded-2xl bg-[#E8C987]/15 px-4 py-3">
                                <p class="font-medium text-slate-700 mb-1">Finalidad opcional, solo si el titular la
                                    autoriza aparte en el Texto 2:</p>
                                <p>Enviar promociones e invitaciones a próximas activaciones de Eurema [y de las marcas
                                    del grupo: lista cerrada].</p>
                            </div>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">6. Autorización</h3>
                            <p>El tratamiento requiere autorización previa, expresa e informada del titular. Se obtiene
                                por WhatsApp antes de usar las atracciones, con registro de fecha, hora, número y texto
                                aceptado, de modo que pueda consultarse después.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">7. Datos sensibles</h3>
                            <p>La declaración sobre las condiciones de salud del menor es un dato sensible. Responderla
                                es facultativo. Si el adulto prefiere no declararla, Eurema puede negar el uso de la
                                atracción por razones de seguridad. Este dato se usa solo para verificar la aptitud del
                                menor y la seguridad en la atracción, y nunca para promociones.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">8. Datos de niños, niñas y adolescentes</h3>
                            <p>Se tratan solo con autorización del padre, la madre o el representante legal, y
                                únicamente para registrar al menor, verificar requisitos y proteger su seguridad. Se
                                respeta el interés superior del menor y su opinión según su madurez. Los datos del menor
                                no se usan para promociones, publicidad ni perfilamiento.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">9. Encargados y proveedores tecnológicos</h3>
                            <p>Spoon trata los datos como encargada. El canal de WhatsApp se presta mediante Meta, y los
                                servidores y copias de respaldo pueden estar dentro o fuera de Colombia. En todos los
                                casos el tratamiento se hace por cuenta de Eurema, bajo contrato de transmisión de
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
                                <li>Oponerse en cualquier momento a recibir promociones, sin que eso afecte su entrada
                                    ni el uso de las atracciones.</li>
                            </ul>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">11. Cómo ejercer los derechos</h3>
                            <p>Por [correo], [WhatsApp] o [teléfono]. La solicitud debe incluir nombre e identificación
                                del titular, descripción de los hechos, dirección de contacto y los documentos que la
                                respalden. Las consultas se responden en máximo diez (10) días hábiles, prorrogables por
                                cinco (5) más. Los reclamos se responden en máximo quince (15) días hábiles,
                                prorrogables por ocho (8) más.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">12. Seguridad</h3>
                            <p>Cifrado de la información, control de accesos y copias de respaldo.</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">13. Conservación y supresión</h3>
                            <p>Al terminar la activación, Spoon entrega la base de datos depurada a Eurema y la borra de
                                sus sistemas en [plazo acordado por escrito entre las partes]. Eurema conserva los datos
                                mientras sea necesario para las finalidades de esta política y la prueba del
                                consentimiento, por [plazo que defina Eurema].</p>
                        </section>

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">14. Vigencia y cambios</h3>
                            <p>Esta política rige desde [fecha]. Si cambia la identificación del responsable o la
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
            <div x-show="promotions" x-cloak x-effect="document.body.classList.toggle('overflow-hidden', promotions)"
                @keydown.escape.window="promotions = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true"
                aria-labelledby="politica-titulo">

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
                            <h2 id="politica-titulo" class="text-lg sm:text-xl font-semibold text-white leading-tight">
                                Política de Promociones
                            </h2>
                        </div>
                        <button type="button" @click="policy = false" title="Cerrar"
                            class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-white/70 hover:bg-white/10 hover:text-white transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Contenido con scroll --}}
                    <div class="flex-1 overflow-y-auto px-6 py-6 text-sm leading-relaxed text-slate-600 space-y-6">

                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-1">1. Responsable</h3>
                            <p>“Yo, {{ $full_name ?? 'Adulto Responsable' }} , mayor de edad, titular de mis propios
                                datos, autorizo de
                                forma
                                libre, previa, expresa e informada a [Eurema], responsable del tratamiento, para que use
                                mi nombre, mi número de WhatsApp con las siguientes finalidades:</p>
                        </section>
                        <section>
                            <h3 class="font-semibold text-[#0A3C55] mb-2">Entiendo y acepto lo siguiente:</h3>
                            <ul class="space-y-1.5 list-disc pl-5 marker:text-[#B8913F]">
                                <li> <span class="font-extrabold">Es voluntaria.</span>Puedo no autorizarla. Mi decisión
                                    no condiciona la compra de la boleta, mi ingreso ni el uso de las atracciones.</li>
                                <li> <span class="font-extrabold">Canales.</span> Puedo elegir por cuáles canales
                                    recibir mensajes: Solo recibiré mensajes por WhatsApp.</li>
                                <li><span class="font-extrabold">Horarios.</span> Los mensajes publicitarios se envían
                                    de lunes a viernes entre las 7:00 a.m. y las 7:00 p.m., y los sábados entre las 8:00
                                    a.m. y las 3:00 p.m. No se envían domingos ni festivos.
                                </li>
                                <li><span class="font-bold">Frecuencia.</span> Recibiré como máximo [número] mensajes
                                    promocionales por [semana o mes].</li>
                                <li><span class="font-extrabold">Vigencia.</span> Esta autorización rige hasta que yo la
                                    revoque.</li>
                                <li><span class="font-extrabold">Datos de menores. </span> Esta autorización no cubre
                                    los datos del menor que me acompaña, que no se usan para promociones ni publicidad.
                                </li>
                                <li><span class="font-extrabold">Terceros.</span> Mis datos no se venden. Solo los usa
                                    Eurema y, por su cuenta, Spoon como encargada de la plataforma.</li>
                                <li><span class="font-extrabold">Puedo revocarla cuando quiera.</span> Mis datos no se
                                    venden. Solo los usa Eurema y, por su cuenta, Spoon como encargada de la plataforma.
                                </li>
                                <li><span class="font-extrabold">Mis derechos.</span> Puedo conocer, actualizar,
                                    rectificar y pedir la supresión de mis datos, y presentar quejas ante la
                                    Superintendencia de Industria y Comercio, según la Política de Tratamiento de Datos
                                    Personales de Eurema.</li>
                                <li><span class="font-extrabold">Prueba.</span> Eurema y Spoon guardan la fecha, la
                                    hora, mi número de WhatsApp y este texto como evidencia de mi decisión.”</li>

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
                        guardar('create');
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
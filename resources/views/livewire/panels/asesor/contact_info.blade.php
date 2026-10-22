<?php


use App\Http\Controllers\LandbotWebhookController;
use App\Traits\traitCruds;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;


new class extends Component
{
    use traitCruds;
    public $noteText;
    public $response;
    public $dataClient;
    public $nameWhatsApp;
    public $telClient;
    public $notes;
    public $conversationId;

    //Modelos del formulario de cliente
    public $id_client;
    public $identificacion;
    public $tipo_documento;
    public $nombre;
    public $apellido;
    public $celular;
    public $genero;
    public $fecha_nacimiento;
    public $email;
    public $direccion;
    public $id_ciudad;
    public $nombre_completo;
    public int $id_park;

    public function unirNombre()
    {
        $this->nombre_completo = $this->nombre . " " . $this->apellido;
    }
    #[On("load-info-client")]
    public function loadData($telClient, $userName, $note = null, $conversationId)
    {
        $this->nameWhatsApp = $userName;
        $this->telClient = $telClient;
        $this->notes = $note;
        $this->conversationId = $conversationId;
        $this->celular = strval($telClient);
        $request = new \Illuminate\Http\Request();
        $request->merge(['search' => $telClient]);
    }
    public function saveNote()
    {
        try {
            $request = new \Illuminate\Http\Request();
            $request->merge(['note' => $this->noteText]);
            $response = app(LandbotWebhookController::class)->saveNote($request, $this->conversationId);
            if ($response['status'] === "success") {
                $this->notes = $this->noteText;
                $this->callNotification($response['message'], $response['status']);
                $this->noteText = "";
            } else {
                $this->callNotification($response['message'], $response['status']);
            }
        } catch (\Throwable $th) {
            $this->handleException($th, 'Ha ocurrido un error al crear la nota del cliente');
        }
    }
};
?>

<div x-data="{ open: false }" x-init="window.addEventListener('toggle-info-cliente', () => open = !open)" x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-x-full"
    x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform translate-x-full"
    class="fixed top-0 right-0 h-full w-full max-w-md lg:w-1/4 bg-white shadow-xl border-l z-50 overflow-y-auto">

    <div class="flex flex-col h-full p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-800">Información del cliente</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>

        <!-- Información principal -->
        <div class="space-y-4 mb-6">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-user text-gray-400"></i>
                <span class="font-medium text-gray-700">
                    {{ $dataClient['data']['nombre_completo'] ?? $nameWhatsApp ?? '--' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-envelope text-gray-400"></i>
                <span class="font-medium text-gray-700">
                    {{ $dataClient['data']['email'] ?? '--' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-phone text-gray-400"></i>
                <span class="font-medium text-gray-700">
                    {{ $dataClient['data']['phone'] ?? $telClient ?? '--' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-venus-mars text-gray-400"></i>
                <span class="font-medium text-gray-700">
                    {{ $dataClient['data']['genero'] ?? '--' }}
                </span>
            </div>
        </div>

        <!-- Notas -->
        <div class="flex-1 flex flex-col">
            <h4 class="font-medium text-gray-800 mb-2">Notas</h4>
            <div class="flex-1 mb-4 p-3 bg-gray-50 rounded-lg border overflow-y-auto text-gray-700">
                {{ $notes ?? 'Sin notas' }}
            </div>

            <form wire:submit.prevent="saveNote" class="relative">
                <textarea wire:model="noteText" x-data @keydown.enter.prevent="
                              if ($event.shiftKey) {
                                  $event.target.value += '\n';
                                  $wire.set('noteText', $event.target.value);
                              } else {
                                  $refs.submitBtn.click();
                              }" rows="2" placeholder="Agregar nota... (Shift+Enter = nueva línea)"
                    class="w-full p-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>

                <button type="submit" x-ref="submitBtn" class="hidden"></button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mensajesPredeterminados = document.querySelectorAll('.message-pred');
        const inputChat = document.getElementById("message-input");
        mensajesPredeterminados.forEach(function(elemento) {
            elemento.addEventListener('click', function(e) {
                inputChat.value = this.textContent;
                inputChat.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            })
        });
    });
</script>
@endpush
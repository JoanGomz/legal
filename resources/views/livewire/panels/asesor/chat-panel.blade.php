<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandbotWebhookController;
use App\Traits\traitCruds;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use PhpParser\Node\Stmt\Echo_;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Aws\S3\S3Client;

new class extends Component
{
    use traitCruds;
    use WithFileUploads;
    public $conversationId;
    public $userName;
    public $conversationStatus;
    public $assigned;
    public $mensajes = [];
    public $canWrite;
    public $status;
    public $text;

    public $attachment;
    public $showConfirmModal = false;
    protected function rulesMessage()
    {
        // MODIFICAR para incluir validación de archivo
        $rules = [];

        if ($this->attachment) {
            $rules['attachment'] = 'file|max:10240';
        } else {
            $rules['text'] = 'required|min:1|string';
        }

        return $rules;
    }
    #[On('activate-conversation')]
    public function activateConversation($conversationId)
    {
        if ($this->conversationId == $conversationId) {
            $request = new \Illuminate\Http\Request();
            $request->merge(['status' => 'activo']);
            $response = app(LandbotWebhookController::class)->changeStatusConversation($request, $conversationId);

            if ($response['status'] === "success") {
                $this->conversationStatus = 'activo';
            }
        }
    }
    //Método llamado desde websocket o evento para actualizar la conversación
    #[On('updateChat')]
    public function updateConversation()
    {
        if (!$this->conversationId) {
            return;
        }

        $newMessages = app(LandbotWebhookController::class)->getConversationHistory($this->conversationId);

        $currentCount = count($this->mensajes['data']['messages'] ?? []);
        $newCount = count($newMessages['data']['messages'] ?? []);

        if ($currentCount === $newCount) {
            // No hay mensajes nuevos, salir sin renderizar

            return;
        }

        // Solo actualizar si hay mensajes nuevos
        $this->mensajes = $newMessages;
        $this->dispatch('scrollChat');
    }
    //Método llamado desde lista de conversaciones para cargar las conversaciones del chat seleccionado
    #[On('load-conversation')]
    public function loadConversation($conversationId, $userName, $status, $canWrite, $user_asing_id = 0)
    {
        $this->userName = $userName;
        $this->conversationStatus = $status;
        $this->assigned = $user_asing_id;
        $this->mensajes = app(LandbotWebhookController::class)->getConversationHistory($conversationId);
        if (!empty($this->mensajes["data"]['messages']) && $this->mensajes["data"]['messages']->count() > 0) {

            $lastMessage = $this->mensajes["data"]['messages']->last();
            // dd($lastMessage);
            $this->dispatch('console-log', data: ['prueba 1' => $lastMessage]);
            if ($lastMessage && isset($lastMessage->created_at)) {
                $this->canWrite = $this->determineCanWrite($lastMessage->created_at);
            } else {
                $this->canWrite = (is_null($lastMessage->created_at) ? $lastMessage->created_at : false);
            }
        } else {
            $this->canWrite = false;
        }
        $this->conversationId = $conversationId;
        $this->dispatch('scroll-chat');
        $this->dispatch('smoothScrollToBottom');
    }
    function determineCanWrite($fecha)
    {
        $lastMessageCarbon = Carbon::parse($fecha);
        $now = Carbon::now();
        if ($lastMessageCarbon->diffInHours($now) > 24) {
            return false;
        }
        return true;
    }
    public function endConversation()
    {
        $request = new \Illuminate\Http\Request();
        $request->merge(['status' => "finalizado"]);
        $this->response = app(LandbotWebhookController::class)->changeStatusConversation($request, $this->conversationId);
        if ($this->response['status'] === "success") {
            $this->dispatch('updateConversations');
            $this->status = "finalizado";
            $this->conversationStatus = $this->status;
            if (Auth::user()->role_id === 14) {
                $this->mensajes = [];
            }
        }
        $this->endPetition();
    }
    public function updatedStatus()
    {
        if ($this->status === "finalizado") {
            $this->showConfirmModal = true;
            $this->status = "pendiente";
            return;
        }
        if ($this->status === "") {
            return;
        }
        $request = new \Illuminate\Http\Request();
        $request->merge(['status' => $this->status]);
        $response = app(LandbotWebhookController::class)->changeStatusConversation($request, $this->conversationId);
        if ($response['status'] === "success") {
            $this->dispatch('updateConversations');
            $this->conversationStatus = $this->status;
        }
    }
    public function updatedAssigned()
    {
        if ($this->assigned === "") {
            return;
        }
        try {
            $request = new \Illuminate\Http\Request();
            $request->merge(['user_id' => $this->assigned]);
            $response = app(LandbotWebhookController::class)->assignUserToConversation($request, $this->conversationId);
            if ($response['status'] === "success") {
                $this->updateConversation();
                $this->callNotification($response['message'], $response['status']);
            } else {
                $this->callNotification($response['message'], $response['status']);
            }
        } catch (\Throwable $th) {
            dump($th);
        }
    }
    public function sendMessage()
    {
        $this->validate($this->rulesMessage());

        $firstMessage = $this->mensajes['data']['messages'][0];
        $request = new \Illuminate\Http\Request();

        // MODIFICAR: Manejar envío de archivo o texto
        if ($this->attachment) {
            $s3Client  = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-2'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $bucket = env('AWS_BUCKET');
            $fileName = time() . '_' . $this->attachment->getClientOriginalName();
            $key = 'Eventrix/chat-files/' . $fileName;

            // Subir archivo SIN ACL
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => file_get_contents($this->attachment->getRealPath()),
                'ContentType' => $this->attachment->getMimeType(),
                // NO incluir 'ACL' => 'public-read'
            ]);

            // Obtener URL
            $url = $result['ObjectURL'];
            $request->merge([
                'messages' => [
                    [
                        '_raw' => [
                            'chat' => $firstMessage['landbot_chat_id'],
                            'author_type' => 'usuario',
                        ],
                        'data' => [
                            'url' => $url,
                        ],
                        'customer' => [
                            'phone' => $firstMessage['customer_phone'],
                            'name' => $this->userName,
                        ]
                    ]
                ]
            ]);
            // Limpiar archivo después de enviar
            $this->attachment = null;
        } else {
            // Tu código existente para texto
            $request->merge([
                'messages' => [
                    [
                        '_raw' => [
                            'chat' => $firstMessage['landbot_chat_id'],
                            'author_type' => 'usuario',
                        ],
                        'data' => [
                            'body' => $this->text
                        ],
                        'customer' => [
                            'phone' => $firstMessage['customer_phone'],
                            'name' => $this->userName,
                        ]
                    ]
                ]
            ]);
        }
        $response = app(LandbotWebhookController::class)->handleWebhook($request);
        if ($response['status'] === "success") {
            $this->updateConversation();
        } else {
            $this->callNotification($response['data'], $response['status']);
        }

        $this->reset('text');
    }

    public function launchBot()
    {
        try {
            $response = app(LandbotWebhookController::class)->restartBot($this->conversationId);

            // Manejar diferentes tipos de respuesta
            $data = [];

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = json_decode($response->getContent(), true);
            } elseif (is_array($response)) {
                $data = $response;
            } else {
                // Fallback si no es ni JsonResponse ni array
                $data = ['message' => 'Bot procesado', 'status' => 'success'];
            }

            $message = $data['message'] ?? 'Bot reiniciado correctamente';
            $status = $data['status'] ?? 'success';

            $this->callNotification($message, $status);
        } catch (\Exception $e) {
            $this->handleException($e, "Error in launchBot");
            $this->callNotification('Error al reiniciar el bot', 'error');
        }
    }
}

?>

<div class="flex-1 flex flex-col resize-x z-10" x-data="{infoCliente: false}">
    <!-- Cabecera de la conversación -->
    <div
        class="relative px-5 py-4 flex bg-slate-800 dark:bg-white/5 dark:backdrop-blur-2xl dark:border-b dark:border-white/10 justify-between items-center font-bold dark:text-white z-20">
        <div class="flex items-center">
            <div class="relative mr-3">
                <div
                    class="w-10 h-10 rounded-full bg-linear-to-br from-[#FD7A0F] via-[#FF3A3B] to-[#C9155D] flex items-center justify-center text-white font-bold shadow-lg shadow-[#FD7A0F]">
                    {{ strtoupper(substr($userName ?? 'C', 0, 2)) }}
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-slate-900 @if ($conversationStatus === 'pendiente') bg-yellow-400
                            @elseif($conversationStatus === 'asignado a')
                                bg-blue-400
                            @elseif($conversationStatus === 'en espera')
                                bg-purple-400
                            @elseif($conversationStatus === 'finalizado')
                                bg-gray-400
                            @else
                                bg-brand-aqua @endif">
                </span>
            </div>
            <div>
                <div class="font-bold dark:text-white text-slate-200">
                    {{ $userName ? (strlen($name = explode(' ', $userName)[0] ?? '') > 2 ? $name : $userName) : '--' }}
                </div>
                <div class="flex items-center text-sm">
                    <span id="estado-chat"
                        class="font-normal text-slate-400 capitalize">{{ $conversationStatus ? $conversationStatus : '--' }}</span>
                </div>
            </div>
        </div>
        <div class="flex gap-2 items-center">
            <button @click="window.dispatchEvent(new CustomEvent('toggle-info-cliente'))"
                class="flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-medium text-slate-300 bg-white/10 backdrop-blur-md border dark:border-slate-600 border-white/10 hover:bg-purple-500/20 hover:text-purple-300 hover:border-purple-400/30 transition-all">
                <i class="fa-solid fa-circle-info text-[11px]"></i>
                Información
            </button>
            <select wire:model.live="status"
                class="form-select text-white text-xs font-medium bg-white/10 backdrop-blur-md border border-white/10 rounded-full px-3.5 py-2 dark:border-slate-600 hover:bg-purple-500/20 hover:border-purple-400/30 transition-all {{ !$canWrite || $conversationStatus === 'finalizado' ? 'cursor-not-allowed opacity-40' : '' }}"
                {{ !$canWrite || $conversationStatus === 'finalizado' ? 'disabled' : '' }}>
                <option value="" class="text-black">Estado</option>
                <option value="en espera" class="text-black">En espera</option>
                <option value="pendiente" class="text-black">Pendiente</option>
                <option value="finalizado" class="text-black">Finalizado</option>
            </select>
        </div>
    </div>
    <!-- Historial de mensajes -->
    <div
        class="flex-1 flex flex-col relative min-h-0 overflow-hidden bg-slate-950 bg-[url('https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1792684115_fondo%20chat%20general%202.png')] bg-cover bg-repeat bg-center">
        <div class="absolute inset-0 bg-black/70 pointer-events-none"></div>
        <div class="pointer-events-none absolute -top-20 -left-20 w-96 h-96 rounded-full bg-purple-600/20 blur-3xl">
        </div>
        <div
            class="pointer-events-none absolute -bottom-20 -right-20 w-96 h-96 rounded-full bg-fuchsia-600/20 blur-3xl">
        </div>
        <div class="pointer-events-none absolute top-1/3 right-1/4 w-72 h-72 rounded-full bg-indigo-600/10 blur-3xl">
        </div>
        <div class="flex-1 flex flex-col relative z-10 h-full ">
            <div class="flex-1 p-4 overflow-y-auto overflow-x-hidden space-y-2" x-data="{ 
        scrollToBottom() { 
            $el.scrollTop = $el.scrollHeight; 
        } 
    }" x-init="scrollToBottom()" @scroll-chat.window="scrollToBottom()" id="content-conversation">
                @if ($mensajes)
                @php
                $count = 0;
                $lastDate;
                @endphp
                @forelse ($mensajes["data"]['messages'] as $item)
                @php
                // Manejar conversation_data de forma segura
                if (is_array($item['conversation_data'])) {
                $conversationData = $item['conversation_data'];
                } elseif (is_string($item['conversation_data'])) {
                $conversationData = json_decode($item['conversation_data'], true) ?: [];
                } else {
                $conversationData = [];
                }

                // Función helper para obtener valores de forma segura
                $getBodyText = function ($data) {
                if (isset($data['body'])) {
                return is_string($data['body'])
                ? $data['body']
                : (isset($data['body']['text'])
                ? $data['body']['text']
                : '');
                }
                return '';
                };

                // Función para verificar si es un mensaje con botones o listas
                $hasInteractiveElements = function ($data) {
                return isset($data['buttons']) || isset($data['action']);
                };

                $lastMessage = $item['created_at'];
                if ($lastMessage) {
                }
                $count++;
                @endphp
                @php
                $currentDate = \Carbon\Carbon::parse($item['created_at'])->format('Y-m-d');
                @endphp

                @if ($count === 1)
                <div class="flex justify-center mb-4">
                    <div
                        class="bg-white/10 backdrop-blur-md text-slate-300 text-[11px] font-medium rounded-full px-3 py-1 border border-white/10">
                        {{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y') }}
                    </div>
                </div>
                @php
                $lastDate = $currentDate;
                @endphp
                @else
                @if ($lastDate !== $currentDate)
                <div class="flex justify-center mb-4">
                    <div
                        class="bg-white/10 backdrop-blur-md text-slate-300 text-[11px] font-medium rounded-full px-3 py-1 border border-white/10">
                        {{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y') }}
                    </div>
                </div>
                @php
                $lastDate = $currentDate;
                @endphp
                @endif
                @endif
                @if ($item['author_type'] === 'client')
                <!-- Mensaje del cliente -->
                <div wire:key="message-{{ $item['id'] ?? $loop->index }}" class="mb-4 flex justify-start min-w-[400px]">
                    <div
                        class="max-w-md rounded-2xl rounded-bl-md p-4 shadow-lg shadow-black/20 {{ $conversationStatus === 'finalizado' ? 'bg-white/5 backdrop-blur-xl border border-white/5 text-slate-500' : 'bg-white/10 backdrop-blur-xl border border-white/10 text-slate-100' }}">

                        @php
                        $extension = isset($conversationData['url'])
                        ? strtolower(pathinfo($conversationData['url'], PATHINFO_EXTENSION))
                        : '';
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
                        $audioExtensions = ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac'];
                        $isImage = in_array($extension, $imageExtensions);
                        $isVideo = in_array($extension, $videoExtensions);
                        $isAudio = in_array($extension, $audioExtensions);
                        @endphp

                        @if (isset($conversationData['url']) && !empty($conversationData['url']))
                        @if ($isImage)
                        {{-- Imagen --}}
                        <div class="mb-2">
                            <img src="{{ $conversationData['url'] }}" alt="Imagen"
                                class="max-w-full h-auto rounded-lg cursor-pointer" style="max-height: 300px;"
                                @click="window.open('{{ $conversationData['url'] }}', '_blank')">
                        </div>
                        @elseif ($isVideo)
                        {{-- Video --}}
                        <div class="mb-2">
                            <video controls class="max-w-full h-auto rounded-lg" style="max-height: 300px;">
                                <source src="{{ $conversationData['url'] }}" type="video/{{ $extension }}">
                                Tu navegador no soporta el elemento de video.
                            </video>
                        </div>
                        @elseif ($isAudio)
                        {{-- Audio --}}
                        <div class="mb-2">
                            <audio controls class="w-[400px]">
                                <source src="{{ $conversationData['url'] }}"
                                    type="audio/{{ $extension == 'oga' ? 'ogg' : $extension }}">
                                Tu navegador no soporta el elemento de audio.
                            </audio>
                        </div>
                        @else
                        {{-- Archivo no reconocido --}}
                        <div class="mb-2">
                            <a href="{{ $conversationData['url'] }}" target="_blank"
                                class="text-purple-300 hover:text-purple-200 underline">
                                <i class="fa-solid fa-file mr-2"></i>Ver archivo
                            </a>
                        </div>
                        @endif

                        {{-- Caption si existe --}}
                        @if (isset($conversationData['caption']) && !empty($conversationData['caption']))
                        <div class="text-sm mt-2">{{ $conversationData['caption'] }}</div>
                        @endif
                        @else
                        {{-- Mensaje de texto normal --}}
                        <div>{!! nl2br(e(str_replace('\n', "\n", $getBodyText($conversationData)))) !!}</div>
                        @endif

                        <div class="text-[10px] mt-1.5 text-slate-400">
                            {{ \Carbon\Carbon::parse($item['created_at'])->format('g:i A') }}
                        </div>
                    </div>
                </div>
                @else
                <!-- Mensaje del asesor -->
                <div wire:key="message-{{ $item['id'] ?? $loop->index }}" class="mb-4 flex justify-end">
                    <div
                        class="max-w-md rounded-2xl rounded-br-md p-4 shadow-lg {{ $conversationStatus === 'finalizado' ? 'bg-white/5 backdrop-blur-xl border border-white/5 text-slate-500' : 'bg-linear-to-br from-[#FD7A0F] via-[#FF3A3B] to-[#C9155D] text-white shadow-[#FD7A0F]/40' }}">

                        @php
                        $extension = isset($conversationData['url'])
                        ? strtolower(pathinfo($conversationData['url'], PATHINFO_EXTENSION))
                        : '';
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
                        $audioExtensions = ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac'];
                        $isImage = in_array($extension, $imageExtensions);
                        $isVideo = in_array($extension, $videoExtensions);
                        $isAudio = in_array($extension, $audioExtensions);
                        @endphp

                        @if (isset($conversationData['url']) && !empty($conversationData['url']))
                        @if ($isImage)
                        {{-- Imagen --}}
                        <div class="mb-2">
                            <img src="{{ $conversationData['url'] }}" alt="Imagen"
                                class="max-w-full h-auto rounded-lg cursor-pointer" style="max-height: 300px;"
                                onclick="window.open('{{ $conversationData['url'] }}', '_blank')">
                        </div>
                        @elseif ($isVideo)
                        {{-- Video --}}
                        <div class="mb-2">
                            <video controls class="max-w-full h-auto rounded-lg" style="max-height: 300px;">
                                <source src="{{ $conversationData['url'] }}" type="video/{{ $extension }}">
                                Tu navegador no soporta el elemento de video.
                            </video>
                        </div>
                        @elseif ($isAudio)
                        {{-- Audio --}}
                        <div class="mb-2">
                            <audio controls class="w-full">
                                <source src="{{ $conversationData['url'] }}"
                                    type="audio/{{ $extension == 'oga' ? 'ogg' : $extension }}">
                                Tu navegador no soporta el elemento de audio.
                            </audio>
                        </div>
                        @else
                        {{-- Archivo no reconocido --}}
                        <div class="mb-2">
                            <a href="{{ $conversationData['url'] }}" target="_blank"
                                class="text-white hover:text-purple-100 underline">
                                <i class="fa-solid fa-file mr-2"></i>Ver archivo
                            </a>
                        </div>
                        @endif

                        {{-- Caption si existe --}}
                        @if (isset($conversationData['caption']) && !empty($conversationData['caption']))
                        <div class="text-sm mt-2">{{ $conversationData['caption'] }}</div>
                        @endif
                        @elseif (!$hasInteractiveElements($conversationData))
                        <!-- Mensaje de texto simple -->
                        <div>{!! nl2br(e(str_replace('\n', "\n", $getBodyText($conversationData)))) !!}</div>
                        @else
                        <!-- Mensaje con elementos interactivos -->

                        <!-- Header del mensaje -->
                        @if (isset($conversationData['header']['text']))
                        <div class="font-bold text-lg mb-2">{{ $conversationData['header']['text'] }}
                        </div>
                        @elseif (isset($conversationData['title']))
                        <div class="font-bold">{{ $conversationData['title'] }}</div>
                        @endif

                        <!-- Cuerpo del mensaje -->
                        @if ($getBodyText($conversationData))
                        <div class="mb-3">{!! nl2br(e(str_replace('\n', "\n", $getBodyText($conversationData)))) !!}
                        </div>
                        @endif

                        <!-- Footer del mensaje -->
                        @if (isset($conversationData['footer']['text']) && $conversationData['footer']['text'])
                        <div class="font-light text-xs mb-3">{{ $conversationData['footer']['text'] }}
                        </div>
                        @elseif (isset($conversationData['footer']) && is_string($conversationData['footer']))
                        <div class="font-light text-xs mb-3">{{ $conversationData['footer'] }}</div>
                        @endif

                        <!-- Botones tradicionales -->
                        @if (isset($conversationData['buttons']) && is_array($conversationData['buttons']))
                        @foreach ($conversationData['buttons'] as $button)
                        <div class="mt-2">
                            <button
                                class="bg-white/15 backdrop-blur-md border border-white/10 hover:bg-white/25 text-white px-4 py-2 rounded-lg w-full text-left transition-colors">
                                {{ is_array($button) ? $button['label'] ?? '' : $button }}
                            </button>
                        </div>
                        @endforeach
                        @endif

                        <!-- WhatsApp List (action con sections) -->
                        @if (isset($conversationData['action']['sections']) &&
                        is_array($conversationData['action']['sections']))
                        <div class="mt-3">
                            @if (isset($conversationData['action']['button']))
                            <div class="text-sm font-medium mb-2 text-purple-100">
                                {{ $conversationData['action']['button'] }}
                            </div>
                            @endif

                            @foreach ($conversationData['action']['sections'] as $section)
                            @if (isset($section['rows']) && is_array($section['rows']))
                            <div class="space-y-1">
                                @foreach ($section['rows'] as $row)
                                <div
                                    class="bg-white/15 backdrop-blur-md border border-white/10 hover:bg-white/25 rounded-lg p-3 cursor-pointer transition-colors">
                                    <div class="font-medium">{{ $row['title'] ?? '' }}
                                    </div>
                                    @if (isset($row['description']) && $row['description'])
                                    <div class="text-xs text-purple-100 mt-1">
                                        {{ $row['description'] }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif
                        @endif
                        <div class="flex justify-end">
                            <div class="text-[10px] mt-1.5 right-2 text-purple-100/70">
                                {{ \Carbon\Carbon::parse($item['created_at'])->format('g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <div class="text-center text-slate-400">No hay mensajes</div>
                @endforelse
                @endif
            </div>
            <div class="p-3">
                <form
                    class="flex items-center gap-2 bg-white/10 backdrop-blur-2xl border border-white/10 shadow-2xl shadow-black/30 rounded-full px-4 py-2"
                    wire:submit.prevent="sendMessage">

                    {{-- Botón reiniciar bot --}}
                    <div class="cursor-pointer w-9 h-9 flex items-center justify-center rounded-full text-purple-300 hover:bg-white/10 transition-colors {{ $conversationStatus === 'finalizado' ? 'cursor-not-allowed opacity-40' : '' }}"
                        title="Reiniciar bot" @if ($canWrite && $conversationStatus !=='finalizado' )
                        wire:click="launchBot" @endif>
                        <i
                            class="fa-solid fa-robot {{ $conversationStatus === 'finalizado' ? 'cursor-not-allowed' : '' }} {{ !$canWrite ? 'cursor-not-allowed' : '' }}"></i>
                    </div>

                    {{-- Botón para adjuntar archivo --}}
                    <div class="relative">
                        <input type="file" id="file-input" wire:model="attachment" class="hidden"
                            accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                            {{ !$canWrite || $conversationStatus === 'finalizado' ? 'disabled' : '' }}>

                        <label for="file-input"
                            class="cursor-pointer w-9 h-9 flex items-center justify-center rounded-full text-slate-300 hover:bg-white/10 hover:text-white transition-colors
                      {{ !$canWrite || $conversationStatus === 'finalizado' ? 'cursor-not-allowed opacity-40 pointer-events-none' : '' }}"
                            title="{{ !$canWrite ? 'Chat bloqueado - 24h transcurridas' : ($conversationStatus === 'finalizado' ? 'Conversación finalizada' : 'Adjuntar archivo') }}">
                            <i class="fa-solid fa-paperclip"></i>
                        </label>

                        {{-- Mostrar archivo seleccionado --}}
                        @if ($attachment)
                        <div
                            class="absolute bottom-full mb-2 left-0 bg-slate-900/90 backdrop-blur-2xl border border-white/10 rounded-xl shadow-2xl shadow-black/50 p-3 text-sm z-50 min-w-[200px]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @php
                                    $ext = strtolower($attachment->getClientOriginalExtension());
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isVideo = in_array($ext, ['mp4', 'avi', 'mov', 'wmv', 'webm']);
                                    $isAudio = in_array($ext, ['mp3', 'wav', 'ogg', 'oga', 'm4a']);
                                    $isPdf = $ext === 'pdf';
                                    @endphp

                                    @if ($isImage)
                                    <i class="fa-solid fa-image text-purple-400 mr-2"></i>
                                    @elseif($isVideo)
                                    <i class="fa-solid fa-video text-fuchsia-400 mr-2"></i>
                                    @elseif($isAudio)
                                    <i class="fa-solid fa-music text-emerald-400 mr-2"></i>
                                    @elseif($isPdf)
                                    <i class="fa-solid fa-file-pdf text-red-400 mr-2"></i>
                                    @else
                                    <i class="fa-solid fa-file text-slate-400 mr-2"></i>
                                    @endif

                                    <span
                                        class="text-slate-200 truncate max-w-[150px]">{{ $attachment->getClientOriginalName() }}</span>
                                </div>
                                <button type="button" wire:click="$set('attachment', null)"
                                    class="ml-3 text-slate-400 hover:text-red-400">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>

                            {{-- Preview para imágenes --}}
                            @if ($isImage)
                            <div class="mt-2">
                                <img src="{{ $attachment->temporaryUrl() }}" class="h-20 w-full object-cover rounded-lg"
                                    alt="Preview">
                            </div>
                            @endif

                            {{-- Tamaño del archivo --}}
                            <div class="text-xs text-slate-400 mt-1">
                                Tamaño: {{ number_format($attachment->getSize() / 1024 / 1024, 2) }} MB
                            </div>
                        </div>
                        @endif

                        {{-- Error de archivo --}}
                        @error('attachment')
                        <div
                            class="absolute bottom-full mb-2 left-0 bg-red-500 text-white text-xs rounded-lg px-2 py-1 z-50">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Contenedor del input Y botones con x-data --}}
                    <div x-data="{ showEmojis: false }" class="h-10 flex-1 flex items-center relative">
                        {{-- Input --}}
                        <textarea wire:model="text" id="message-input" rows="1"
                            placeholder="{{ !$canWrite ? 'Chat bloqueado - 24h transcurridas' : ($conversationStatus === 'finalizado' ? 'Conversación finalizada' : ($attachment ? '' : 'Escribe tu respuesta...')) }}"
                            {{ !$canWrite || $conversationStatus === 'finalizado' ? 'disabled' : '' }}
                            autocomplete="off"
                            @class([ 'h-10 w-full bg-transparent border-none outline-none resize-none'
                            , 'text-white placeholder-slate-400'=> $canWrite && $conversationStatus !== 'finalizado',
                        'cursor-not-allowed text-slate-500' =>
                            !$canWrite || $conversationStatus === 'finalizado',
                    ])"
                    @keydown.enter.prevent="
                                if ($event.shiftKey) {
                                    $event.target.value += '\n';
                                    $wire.set('text', $event.target.value);
                                } else {
                                    $refs.submitBtn.click();
                                }
                            "></textarea>

                        {{-- Botones DENTRO del div con x-data --}}
                        <div class="flex space-x-1 ml-2">
                            <button type="button" @click="$wire.set('text', '')"
                                class="w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:bg-white/10 hover:text-white transition-colors
                           {{ $conversationStatus === 'finalizado' || !$canWrite ? 'cursor-not-allowed opacity-40' : '' }}"
                                {{ $conversationStatus === 'finalizado' || !$canWrite ? 'disabled' : '' }}
                                title="Limpiar texto">
                                <i class="fa-solid fa-eraser text-sm"></i>
                            </button>

                            <button type="button" @click="showEmojis = !showEmojis"
                                class="w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:bg-white/10 hover:text-white transition-colors
                           {{ $conversationStatus === 'finalizado' || !$canWrite ? 'cursor-not-allowed opacity-40' : '' }}"
                                {{ $conversationStatus === 'finalizado' || !$canWrite ? 'disabled' : '' }}
                                title="Emojis">
                                <i class="fa-solid fa-face-smile text-sm"></i>
                            </button>
                        </div>

                        {{-- Modal de emojis --}}
                        <div x-show="showEmojis" x-transition @click.away="showEmojis = false"
                            class="absolute bottom-full mb-2 right-0 z-50 bg-slate-900/90 backdrop-blur-2xl rounded-xl shadow-2xl shadow-black/50 border border-white/10">
                            <emoji-picker
                                @emoji-click="$wire.set('text', ($wire.text || '') + $event.data.unicode); showEmojis = false"
                                class="border-0">
                            </emoji-picker>
                        </div>

                        {{-- Error de texto --}}
                        @error('text')
                        <div class="text-red-400 text-xs mt-1 absolute top-full left-0">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Botón enviar --}}
                    <button type="submit" x-ref="submitBtn" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-progress"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-linear-to-br from-purple-500 to-fuchsia-600 text-white shadow-lg shadow-purple-900/50 transition-all shrink-0
               {{ $conversationStatus === 'finalizado' || !$canWrite ? 'cursor-not-allowed opacity-40' : 'hover:shadow-purple-500/50' }}"
                        {{ $conversationStatus === 'finalizado' || !$canWrite ? 'disabled' : '' }}
                        title="{{ !$canWrite ? 'Chat bloqueado' : ($conversationStatus === 'finalizado' ? 'Conversación finalizada' : 'Enviar mensaje') }}">
                        @if ($attachment)
                        <i class="fa-solid fa-paperclip text-sm"></i>
                        @else
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
    @if ($showConfirmModal)
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-50"
        wire:click="$set('showConfirmModal', false)">
        <div class="bg-slate-900/95 backdrop-blur-2xl border border-white/10 rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl shadow-black/50"
            @click.stop>
            <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mb-4">
                <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-white mb-1.5">Confirmar Finalización</h3>
            <p class="text-slate-400 text-sm mb-6">¿Estás seguro de que deseas finalizar esta conversación?</p>

            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showConfirmModal', false)"
                    class="px-4 py-2 text-sm font-medium text-slate-300 border border-white/10 rounded-lg hover:bg-white/10 transition-colors">
                    Cancelar
                </button>
                <button wire:click="endConversation"
                    onclick="window.dispatchEvent(new CustomEvent('show-loading', { detail: { message: 'Cargando...' } }));"
                    class="px-4 py-2 text-sm font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Finalizar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@push('scripts')
@script
<script>
    const messagesContainer = document.getElementById("content-conversation");

    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function showBrowserNotification(data) {
        if (Notification.permission !== 'granted') return;

        if (data?.message?.author_type !== 'cliente') return;

        const bodyText = data?.message?.conversation_data?.body ?? 'Nuevo mensaje recibido';

        const notification = new Notification('Nuevo mensaje', {
            body: typeof bodyText === 'string' ? bodyText : 'Nuevo mensaje recibido',
            icon: '/favicon.ico',
            tag: 'chat' + (data?.conversation_id ?? 'general'),
        });

        notification.onclick = () => {
            window.focus();
            notification.close();
        };
    }

    const conectarPrueba = () => {
        if (window.ably && window.ably.connection.state === 'connected') {
            const channel = window.ably.channels.get('legal');
            channel.subscribe('MessageSent', function(message) {
                Livewire.dispatch('updateChat');
                Livewire.dispatch('updateConversations');

                showBrowserNotification(message.data);
            });
            channel.subscribe('ConversationUpdate', function(message) {
                Livewire.dispatch('updateChat');
                scrollChat();
            });
        } else {
            setTimeout(conectarPrueba, 200);
        }
    };

    conectarPrueba();

    function scrollChat() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    document.addEventListener('scrollChat', function() {
        scrollChat();
    });
    async function initPush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        const registration = await navigator.serviceWorker.register('/sw.js');
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: vapidKey,
        });

        // Envía la suscripción a tu backend
        fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(subscription),
        });
    }

    initPush();
</script>
@endscript
@endpush
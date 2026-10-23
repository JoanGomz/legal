<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\LandbotWebhookController;

new class extends Component
{
    public $selectedConversationId;
    public $firstLoad = 0;
    public $search = '';
    public $status = '';
    public $limit = 15;
    public $total = 0;
    public $conversations = ['data' => []];

    private function getConversationsData()
    {
        $userId = (Auth::user()->role_id === 3)
            ? Auth::user()->id
            : null;

        $response = app(LandbotWebhookController::class)->getAllConversations(
            $this->status,
            $userId,
            $this->search
        );

        // Por si el controlador devuelve un JsonResponse en vez de un array
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response = $response->getData(true);
        }

        $data = collect($response['data'] ?? []);

        $this->total = $data->count();
        $response['data'] = $data->take($this->limit)->all();

        return $this->conversations = $response;
    }

    #[On('updateConversations')]
    public function actualizar()
    {
        // OJO: ya no se limpia $this->status aquí. Antes, cada mensaje nuevo
        // que llegaba por Ably borraba el filtro que el asesor había elegido.
        if ($this->selectedConversationId) {
            $this->getConversationsData();

            $selected = collect($this->conversations['data'] ?? [])
                ->firstWhere('id', $this->selectedConversationId);

            if ($selected) {
                $this->dispatchDetails($selected);
            }
        }
    }

    public function loadMoreConversation()
    {
        $this->limit += 5;
    }

    public function selectConversation($conversationId, $userName, $status, $telefono, $nota = null, $updated_at = null, $user_asing_id = null)
    {
        if ($conversationId == $this->selectedConversationId && $this->firstLoad === 2) return;
        $this->firstLoad = 2;
        $this->selectedConversationId = $conversationId;

        $this->dispatchDetails([
            'id' => $conversationId,
            'name' => $userName,
            'status' => $status,
            'phone' => $telefono,
            'note' => $nota,
            'updated_at' => $updated_at,
            'user_asing_id' => $user_asing_id
        ]);

        if ($status !== 'activo') {
            $this->dispatch('activate-conversation', $conversationId)->to('panels.asesor.chat_panel');
        }
    }

    private function dispatchDetails($data)
    {
        $canWrite = !empty($data['updated_at']) && Carbon::parse($data['updated_at'])->diffInHours(Carbon::now()) <= 24;

        $this->dispatch(
            'load-conversation',
            conversationId: $data['id'],
            userName: $data['name'],
            status: $data['status'],
            canWrite: $canWrite,
            user_asing_id: $data['user_asing_id'] ?? null
        );

        $this->dispatch(
            'load-info-client',
            telClient: $data['phone'],
            userName: $data['name'],
            note: $data['note'] ?? null,
            conversationId: $data['id']
        )->to('panels.asesor.contact_info');
    }

    public function with()
    {
        try {
            $this->conversations = $this->getConversationsData();
            $this->lastQuery = md5($this->search . $this->status);
            // Auto-selección en la primera carga
            if ($this->firstLoad == 0 && !empty($conversations['data'])) {
                $this->firstLoad = 1;
                $first = $this->conversations['data'][0];
                $this->selectedConversationId = $first['id'];

                $args = implode(', ', array_map('json_encode', [$first['id'], $first['name'] ?? '--', $first['status'] ?? '', $first['phone'] ?? '', $first['note'] ?? '', $first['updated_at'] ?? null, $first['user_asing_id'] ?? null,]));
                $this->js("setTimeout(() => { \$wire.selectConversation({$args}); }, 100);");
            }
        } catch (\Throwable $e) {
            $this->conversations = ['data' => []];
            $this->total = 0;
            session()->flash('error', 'Error al cargar conversaciones: ' . $e->getMessage());
        }

        return ['conversations' => $this->conversations, 'total' => $this->total];
    }
}
?>

<div
    class="w-full lg:w-1/4 min-w-[300px] flex flex-col min-h-0 overflow-y-auto resize-x z-20 bg-[#F7F8FA] border-r border-slate-200/70">

    <div class="sticky top-0 z-30 bg-[#F7F8FA]/95 backdrop-blur-sm px-4 pt-4 pb-3 space-y-3">

        <div class="flex gap-2 items-center">
            <div class="flex-1 relative">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input id="searchInput" type="text" wire:model.live.debounce.400ms="search"
                    placeholder="Buscar conversación"
                    class="h-10 w-full rounded-full pl-10 pr-4 bg-white border border-slate-200 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:outline-none focus:border-[#0A3C55]/40 focus:ring-2 focus:ring-[#0A3C55]/15 transition-all duration-200">
            </div>
            <button type="button" wire:click="actualizar" wire:loading.attr="disabled" title="Actualizar"
                class="w-10 h-10 shrink-0 rounded-full bg-[#0A3C55] flex items-center justify-center shadow-md shadow-[#0A3C55]/25 hover:bg-[#0F5E83] transition duration-200">
                <i class="fa-solid fa-rotate-right text-[#E8C987] text-sm" wire:loading.class="animate-spin"
                    wire:target="actualizar"></i>
            </button>
        </div>

        {{-- Filtro de estado --}}
        <div class="relative" x-data="{
                open: false,
                documentType: @entangle('status').live,
                options: [
                    { value: '', label: 'Todos' },
                    { value: 'en espera', label: 'En espera' },
                    { value: 'pendiente', label: 'Pendiente' },
                    { value: 'finalizado', label: 'Finalizado' },
                ],
                get selectedLabel() {
                    const found = this.options.find(o => o.value === this.documentType);
                    return found ? found.label : 'Todos';
                },
                select(option) {
                    this.documentType = option.value;
                    this.open = false;
                }
            }" @click.outside="open = false">

            <button type="button" id="type" @click="open = !open"
                class="w-full h-10 flex items-center justify-between text-left bg-white border border-slate-200 text-sm text-slate-700 rounded-full shadow-sm px-4 transition-all duration-200 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A3C55]/15"
                :class="open && 'border-[#0A3C55]/40 ring-2 ring-[#0A3C55]/15'">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-filter text-xs text-[#B8913F]"></i>
                    <span class="text-slate-400">Estado:</span>
                    <span x-text="selectedLabel" class="font-medium text-[#0A3C55]"></span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-2"
                    :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute z-50 mt-2 w-full bg-white border border-slate-100 rounded-2xl shadow-xl shadow-black/10 overflow-hidden"
                style="display: none;">
                <ul class="py-1.5">
                    <template x-for="option in options" :key="option.value">
                        <li @click="select(option)"
                            class="mx-1.5 flex items-center justify-between px-3 py-2 text-sm rounded-xl cursor-pointer text-slate-700 transition-colors duration-150 hover:bg-slate-100"
                            :class="documentType === option.value && 'bg-[#0A3C55]/5 text-[#0A3C55] font-medium'">
                            <span x-text="option.label"></span>
                            <i x-show="documentType === option.value"
                                class="fa-solid fa-check text-xs text-[#B8913F]"></i>
                        </li>
                    </template>
                </ul>
            </div>

            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>

    <div class="relative z-0 px-2 pb-3 space-y-1">
        @forelse ($conversations['data'] as $item)
        @php
        $itemObj = is_array($item) ? (object)$item : $item;
        $isSelected = $selectedConversationId === $itemObj->id;

        // Extracción compacta del mensaje
        $messageText = 'Sin mensaje';
        if (isset($item['lastMessage']['conversation_data'])) {
        $data = is_string($item['lastMessage']['conversation_data'])
        ? json_decode($item['lastMessage']['conversation_data'], true) ?? []
        : $item['lastMessage']['conversation_data'];

        $messageText = $data['body']['text'] ??
        (is_string($data['body'] ?? null) ? $data['body'] : null) ??
        $data['header']['text'] ??
        $data['text'] ??
        (isset($data['action']['button']) ? 'Menú: ' . $data['action']['button'] : 'Sin mensaje');
        }

        $nombre = $item['client']->name ?? $item['name'] ?? '--';
        $iniciales = mb_strtoupper(mb_substr($nombre, 0, 2));
        $hora = !empty($item['updated_at']) ? \Carbon\Carbon::parse($item['updated_at']) : null;

        $punto = match ($itemObj->status) {
        'pendiente' => 'bg-amber-400',
        'en espera' => 'bg-sky-400',
        'finalizado' => 'bg-slate-400',
        default => 'bg-emerald-400',
        };
        @endphp

        <div wire:key="conv-{{ $item['id'] }}"
            class="chat-item relative flex items-center gap-3 px-3 py-3 rounded-2xl cursor-pointer transition-all duration-200 {{ $isSelected ? 'bg-gradient-to-r from-[#0A3C55] to-[#14678C] shadow-lg shadow-[#0A3C55]/25' : 'hover:bg-white hover:shadow-sm' }}"
            wire:click="selectConversation({{ json_encode($item['id']) }}, {{ json_encode($item['name'] ?? '--') }}, {{ json_encode($item['status']) }}, {{ json_encode($item['phone']) }}, {{ json_encode($item['note'] ?? '') }}, {{ json_encode($item['updated_at']) }}, {{ json_encode($item['user_asing_id']) }})"
            data-status="{{ strtolower($itemObj->status) }}">

            {{-- Acento dorado del seleccionado --}}
            @if ($isSelected)
            <span class="absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-[#E8C987]"></span>
            @endif

            {{-- Avatar con estado --}}
            <div class="relative shrink-0">
                <div
                    class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-bold {{ $isSelected ? 'bg-gradient-to-br from-[#E8C987] to-[#B8913F] text-[#0A3C55]' : 'bg-[#0A3C55]/10 text-[#0A3C55]' }}">
                    {{ $iniciales }}
                </div>
                <span
                    class="absolute bottom-0 right-0 w-3 h-3 rounded-full ring-2 {{ $isSelected ? 'ring-[#0F4F6E]' : 'ring-[#F7F8FA]' }} {{ $punto }}"></span>
            </div>

            {{-- Texto --}}
            <div class="min-w-0 flex-1">
                <div class="flex justify-between items-baseline gap-2">
                    <div class="font-semibold truncate {{ $isSelected ? 'text-white' : 'text-slate-800' }}">
                        {{ $nombre }}
                    </div>
                    @if ($hora)
                    <div class="text-[11px] whitespace-nowrap {{ $isSelected ? 'text-[#E8C987]' : 'text-slate-400' }}">
                        {{ $hora->isToday() ? $hora->format('g:i A') : ($hora->isYesterday() ? 'Ayer' : $hora->format('d/m/y')) }}
                    </div>
                    @endif
                </div>

                <div class="text-xs truncate mt-0.5 {{ $isSelected ? 'text-white/75' : 'text-slate-500' }}">
                    {{ $messageText }}
                </div>

                <div class="flex items-center justify-between mt-1.5">
                    <span class="text-[11px] {{ $isSelected ? 'text-white/60' : 'text-slate-400' }}">
                        <i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $itemObj->phone }}
                    </span>
                    <span
                        class="text-[10px] font-semibold capitalize rounded-full px-2 py-0.5 {{ $isSelected ? 'bg-white/15 text-white' : ($itemObj->status === 'pendiente' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200/70 text-slate-600') }}">
                        {{ $itemObj->status }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-slate-400 text-center">
            <i class="fa-regular fa-comments mb-2 text-2xl opacity-60"></i>
            <p class="text-sm">No hay conversaciones</p>
        </div>
        @endforelse

        @if (count($conversations['data']) < $total) <div class="pt-2 px-2">
            <button type="button" wire:click="loadMoreConversation" wire:loading.attr="disabled"
                wire:target="loadMoreConversation"
                class="w-full inline-flex items-center justify-center gap-2 rounded-full border border-[#0A3C55]/20 bg-white px-4 py-2.5 text-sm font-semibold text-[#0A3C55] shadow-sm transition hover:bg-[#0A3C55] hover:text-white active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70">
                <span wire:loading.remove wire:target="loadMoreConversation" class="whitespace-nowrap">
                    Cargar más conversaciones
                </span>
                <span wire:loading.flex wire:target="loadMoreConversation"
                    class="items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    Cargando...
                </span>
            </button>
    </div>
    @elseif (count($conversations['data']) > 0)
    <p class="pt-3 text-center text-[11px] text-slate-400">
        Todas las conversaciones están visibles
    </p>
    @endif
</div>
</div>
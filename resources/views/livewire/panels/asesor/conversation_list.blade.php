<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\LandbotWebhookController;

new class extends Component
{
    public $selectedConversationId;
    public $loading = false;
    public $firstLoad = 0;
    public $search = '';
    public $status = '';
    public $lastQuery = '';
    public $limit = 15;
    public $total = 0;
    public $conversations;

    private function getConversationsData()
    {
        $userId = (Auth::user()->role_id === 3)
            ? Auth::user()->id
            : null;

        $this->conversations = app(LandbotWebhookController::class)->getAllConversations(
            $this->status,
            $userId,
            $this->search
        );
        $this->total = count($this->conversations['data']);

        if (isset($this->conversations['data'])) {
            $this->conversations['data'] = collect($this->conversations['data'])->take($this->limit)->all();
        }

        return $this->conversations;
    }

    private function determineCanWrite($fecha)
    {
        if (!$fecha) return false;
        return Carbon::parse($fecha)->diffInHours(Carbon::now()) <= 24;
    }

    #[On('updateConversations')]
    public function actualizar()
    {
        $this->status = ''; // Limpia el status según tu lógica original

        if ($this->selectedConversationId) {
            $this->conversations = $this->getConversationsData();
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
        if ($conversationId === $this->selectedConversationId) return;
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
            $this->dispatch('activate-conversation', $conversationId)->to('whatsapp_panels.chat_panel');
        }
    }

    private function dispatchdatas($data)
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
        )->to('whatsapp_panels.contact_info');
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
                $this->js("setTimeout(() => { \$wire.selectConversation('{$first['id']}', " . json_encode($first['name']) . ", '{$first['status']}', '{$first['phone']}', " . json_encode($first['note']) . ", '{$first['updated_at']}', " . ($first['user_asing_id'] ?? 'null') . "); }, 100);");
            }
        } catch (\Exception $e) {
            $conversations = ['data' => []];
            session()->flash('error', 'Error al cargar conversaciones: ' . $e->getMessage());
        }

        return ['conversations' => $this->conversations, 'total' => $this->total];
    }
}
?>

<div
    class="w-full lg:w-1/4 min-w-75 overflow-y-auto resize-x z-20 shadowCard bg-white dark:bg-linear-to-b dark:from-[#182238] dark:via-[#1B263D] dark:to-[#202A45] border-r border-slate-200 dark:border-white/10">

    {{-- BUSCADOR --}}
    <div
        class="p-4 border-b border-slate-200 dark:border-white/10 bg-white/90 dark:bg-white/2.5 backdrop-blur-xl flex flex-wrap gap-2 items-center">
        <div class="flex-1 relative">
            <i
                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-purple-400 dark:text-purple-300/70"></i>
            <input id="searchInput" type="text" wire:model.live="search" placeholder="Buscar conversación"
                class="h-10 w-full rounded-xl pl-9 pr-3 bg-slate-50 dark:bg-white/6 border border-slate-200 dark:border-white/10 text-purple-900 dark:text-slate-100 placeholder:text-purple-400 dark:placeholder:text-slate-400 backdrop-blur-xl focus:outline-none focus:border-[#C026D3]/60 focus:ring-2 focus:ring-[#C026D3]/20 transition-all duration-200">
        </div>
        <button type="button" wire:click="actualizar" wire:loading.attr="disabled"
            class="w-10 h-10 rounded-full bg-blue-50 dark:bg-sky-400/10 border border-blue-100 dark:border-sky-400/20 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-sky-400/20 hover:border-blue-200 dark:hover:border-sky-400/30 transition duration-200">
            <i class="fa-solid fa-rotate-right text-blue-600 dark:text-sky-300 text-sm transition-colors duration-200"
                wire:loading.class="animate-spin"></i>
        </button>
    </div>

    <div
        class="relative p-3 border-b border-slate-200 dark:border-white/10 bg-white/90 dark:bg-linear-to-r dark:from-[#1A2338]/90 dark:via-[#202B45]/90 dark:to-[#252C4B]/90 backdrop-blur-xl">
        <div
            class="pointer-events-none absolute -right-10 -top-10 hidden dark:block h-24 w-24 rounded-full bg-[#C026D3]/10 blur-3xl">
        </div>
        <label for="status"
            class="relative block mb-2 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-purple-200/80">Seleccione
            estado</label>
        <div class="relative">
            <select wire:model.live="status" id="status"
                class="relative block w-full appearance-none rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] p-2.5 pr-10 text-sm text-slate-700 dark:text-slate-100 shadow-sm dark:shadow-none transition-all duration-200 hover:border-purple-300 dark:hover:border-purple-400/30 focus:border-purple-500 dark:focus:border-[#C026D3]/60 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:focus:ring-[#C026D3]/25">
                <option value="" class="bg-white text-slate-700 dark:bg-[#20283F] dark:text-white">Todos</option>
                <option value="en espera" class="bg-white text-slate-700 dark:bg-[#20283F] dark:text-white">En espera
                </option>
                <option value="pendiente" class="bg-white text-slate-700 dark:bg-[#20283F] dark:text-white">Pendiente
                </option>
                <option value="finalizado" class="bg-white text-slate-700 dark:bg-[#20283F] dark:text-white">Finalizado
                </option>
            </select>
            <i
                class="fa-solid fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 dark:text-purple-300/70"></i>
        </div>
    </div>

    {{-- LISTA DE CONVERSACIONES --}}
    <div class="relative">
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
        @endphp

        <div class="chat-item relative p-4 border-b border-slate-200/80 dark:border-white/6 cursor-pointer transition-all duration-200 {{ $isSelected ? 'bg-linear-to-r from-purple-100 via-violet-50 to-fuchsia-50 border-l-4 border-l-[#C026D3] shadow-[inset_0_0_18px_rgba(192,38,211,0.08)] text-purple-900 dark:from-[#C026D3]/30 dark:via-[#312E81]/30 dark:to-[#C026D3]/20 dark:border-l-2 dark:border-l-[#C026D3] dark:shadow-[inset_0_0_25px_rgba(192,38,211,0.08)] dark:text-white' : 'bg-transparent hover:bg-slate-100/80 dark:hover:bg-white/[0.055] dark:hover:border-l-2 dark:hover:border-l-purple-400/40' }}"
            wire:click="selectConversation({{ json_encode($item['id']) }}, {{ json_encode($item['name'] ?? '--') }}, {{ json_encode($item['status']) }}, {{ json_encode($item['phone']) }}, {{ json_encode($item['note'] ?? '') }}, {{ json_encode($item['updated_at']) }}, {{ json_encode($item['user_asing_id']) }})"
            data-status="{{ strtolower($itemObj->status) }}">

            @if ($isSelected)
            <div
                class="pointer-events-none absolute inset-0 bg-linear-to-r from-[#C026D3]/5 via-transparent to-[#6366F1]/5 dark:from-[#C026D3]/10 dark:to-[#6366F1]/10">
            </div>
            @endif

            <div class="relative">
                <div class="flex justify-between items-start mb-1">
                    <div
                        class="font-semibold truncate {{ $isSelected ? 'text-purple-900 dark:text-white' : 'text-slate-800 dark:text-slate-100' }}">
                        {{ $item['client']->name ?? $item['name'] ?? '--' }}
                    </div>
                    <div
                        class="text-xs ml-2 whitespace-nowrap {{ $isSelected ? 'text-purple-700 dark:text-purple-100/80' : 'text-gray-500 dark:text-slate-400' }}">
                        {{ $itemObj->phone }}
                    </div>
                </div>

                <div
                    class="text-xs mb-2 truncate {{ $isSelected ? 'text-purple-700 dark:text-purple-100/75' : 'text-gray-600 dark:text-slate-400' }}">
                    {{ $messageText }}
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span
                            class="w-2 h-2 rounded-full mr-2 {{ $itemObj->status === 'pendiente' ? 'bg-yellow-400 shadow-[0_0_7px_rgba(250,204,21,0.45)]' : ($itemObj->status === 'en espera' ? 'bg-purple-400 shadow-[0_0_7px_rgba(192,132,252,0.45)]' : ($itemObj->status === 'finalizado' ? 'bg-gray-400' : 'bg-blue-400 shadow-[0_0_7px_rgba(96,165,250,0.4)]')) }}"></span>
                        <span
                            class="text-xs capitalize {{ $isSelected ? 'text-purple-700 dark:text-purple-100/90' : 'text-gray-700 dark:text-slate-300' }}">
                            {{ $itemObj->status }}
                        </span>
                    </div>

                    @if ($itemObj->status === 'pendiente')
                    <span
                        class="bg-red-500 text-white text-xs rounded-full w-2 h-2 flex items-center justify-center shadow-[0_0_7px_rgba(239,68,68,0.5)]"></span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-6 text-gray-500 dark:text-slate-400 text-center">
            <i class="fa-regular fa-comments mb-2 text-xl opacity-50"></i>
            <p class="text-sm">No hay conversaciones</p>
        </div>
        @endforelse
        @if (count($conversations['data']) < $total) <div class="flex w-full items-center justify-center p-2 text-sm">
            <button type="button" wire:click="loadMoreConversation" wire:loading.attr="disabled"
                wire:target="loadMoreConversation"
                class="w-full inline-flex items-center justify-center border gap-2 rounded-3xl bg-linear-to-r from-[#FD7A0F] via-[#FF3A3B] to-[#C9155D] px-4 py-3 font-semibold text-white transition hover:brightness-110 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70 border-amber-700">
                <span wire:loading.remove wire:target="loadMoreConversation" class="whitespace-nowrap">
                    Cargar más mensajes
                </span>
                <span wire:loading.flex wire:target="loadMoreConversation"
                    class="items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="h-4 w-4 shrink-0 animate-spin text-white" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>Cargando...</span>
                </span>
            </button>
    </div>
    @else
    <div class="flex w-full bg-transparent items-center justify-center text-md p-2">
        <button
            class="flex w-full bg-linear-to-r from-[#FD7A0F] via-[#FF3A3B] to-[#C9155D] text-white font-bold border rounded-3xl justify-center p-4 m-2 border-amber-700">
            <span wire:loading.remove wire:target="loadMoreConversation">
                Todas las conversaciones estan visibles
            </span>
        </button>
    </div>
    @endif
</div>
</div>
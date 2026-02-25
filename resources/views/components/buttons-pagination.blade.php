@props(['data'])

<!-- Paginación -->
<div class="flex justify-center items-center mb-4 mt-4">
    <div class="buttons flex gap-2">
        @php
            $isPaginated = false;
            $currentPage = 1;
            $lastPage = 1;

            if (isset($data['data'])) {
                if (is_object($data['data']) && method_exists($data['data'], 'lastPage')) {
                    $currentPage = $data['data']->currentPage();
                    $lastPage = $data['data']->lastPage();
                    $isPaginated = true;
                }
                elseif (is_array($data['data']) && isset($data['meta']['last_page'])) {
                    $currentPage = $data['meta']['current_page'];
                    $lastPage = $data['meta']['last_page'];
                    $isPaginated = true;
                }
            }

            $window = 3;
            $startPage = max($currentPage - $window, 1);
            $endPage = min($currentPage + $window, $lastPage);

            $showStartDots = $startPage > 1;
            $showEndDots = $endPage < $lastPage;
        @endphp

        @if ($isPaginated)
            <!-- Botón de página anterior -->
            @if ($currentPage > 1)
                <button type="button" aria-label="Botón página anterior" wire:click="goToPage({{ $currentPage - 1 }})" wire:loading.class="cursor-progress opactity-50" wire:loading.atrr="disabled"
                    class="p-2 px-3 bg-gray-200 text-gray-700 rounded">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            @endif

            <!-- Primera página siempre visible -->
            @if ($startPage > 1)
                <button type="button" wire:click="goToPage(1)" wire:loading.class="cursor-progress opactity-50" wire:loading.atrr="disabled"
                    class="p-2 px-3 rounded {{ $currentPage == 1 ? 'bg-slate-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                    1
                </button>

                <!-- Puntos suspensivos al inicio si es necesario -->
                @if ($showStartDots && $startPage > 2)
                    <span class="p-2 px-3">...</span>
                @endif
            @endif

            <!-- Páginas intermedias -->
            @for ($i = $startPage; $i <= $endPage; $i++)
                <button  type="button" wire:click="goToPage({{ $i }})" wire:loading.class="cursor-progress opactity-50" wire:loading.atrr="disabled"
                    class="p-2 px-3 rounded {{ $currentPage == $i ? 'bg-slate-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ $i }}
                </button>
            @endfor

            <!-- Puntos suspensivos al final si es necesario -->
            @if ($showEndDots && $endPage < $lastPage - 1)
                <span class="p-2 px-3">...</span>
            @endif

            <!-- Última página siempre visible si no es igual a la actual -->
            @if ($endPage < $lastPage)
                <button type="button" wire:click="goToPage({{ $lastPage }})" wire:loading.class="cursor-progress opactity-50" wire:loading.atrr="disabled"
                    class="p-2 px-3 rounded {{ $currentPage == $lastPage ? 'bg-slate-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ $lastPage }}
                </button>
            @endif

            <!-- Botón de página siguiente -->
            @if ($currentPage < $lastPage)
                <button type="button" aria-label="Botón página siguiente" wire:click="goToPage({{ $currentPage + 1 }})" wire:loading.class="cursor-progress opactity-50" wire:loading.atrr="disabled"
                    class="p-2 px-3 bg-gray-200 text-gray-700 rounded">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @endif
        @elseif ($lastPage == 1)
            <!-- Si solo hay una página, mostrar solo el botón 1 -->
            <button type="button" class="p-2 px-3 rounded bg-slate-700 text-white">
                1
            </button>
        @endif
    </div>
</div>

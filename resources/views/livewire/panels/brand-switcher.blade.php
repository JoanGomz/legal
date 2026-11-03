<?php

use App\Http\Controllers\Base\BrandsController;
use App\Models\Base\Brands;
use App\Traits\WithBrandFilter;
use Livewire\Volt\Component;

new class extends Component {
    use WithBrandFilter;

    public function select(int $id): void
    {
        abort_unless(auth()->user()->hasRole('SuperAdmin'), 403);

        session(['brand_id' => $id]);
        $this->brand = $id;

        $this->dispatch('brand-changed', brandId: $id);
    }

    public function with(): array
    {
        $brands = app(BrandsController::class)->indexPaginated(1, 50, '')['data'] ?? [];
        $active = Brands::whereKey($this->brand)->value('name');

        return [
            'brands'       => $brands,
            'activeName'   => $active ?? null,
            'isSuperAdmin' => auth()->user()->hasRole('SuperAdmin'),
        ];
    }
}; ?>

@php
$initials = fn ($name) => collect(explode(' ', $name))
->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
@endphp

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">

    <button type="button" @if ($isSuperAdmin) @click="open = !open" :aria-expanded="open" @else disabled @endif class="flex items-center gap-2.5 h-11 pl-1.5 pr-3.5 rounded-full border border-slate-300 bg-white
               {{ $isSuperAdmin ? 'cursor-pointer hover:border-slate-400' : 'cursor-default hidden' }}">
        <span
            class="w-8 h-8 rounded-full bg-[#01305A] text-white flex items-center justify-center text-xs font-extrabold">
            {{ $activeName ? $initials($activeName) : '?' }}
        </span>
        <span class="flex flex-col items-start leading-tight">
            <span class="text-[11px] text-slate-500">Marca activa</span>
            <span class="text-sm font-bold text-slate-900">{{ $activeName ?? 'Elegir marca' }}</span>
        </span>
        @if ($isSuperAdmin)
        <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition-transform"
            :class="open && 'rotate-180'"></i>
        @endif
    </button>

    @if ($isSuperAdmin)
    <ul x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="absolute right-0 mt-2 w-64 p-1.5 rounded-2xl z-50 origin-top-right
                   bg-gradient-to-br from-white/80 to-transparent
                   backdrop-blur-[4px] border border-[#01305A] ring-1 ring-slate-900/5
                   shadow-2xl shadow-slate-900/15">
        @foreach ($brands as $b)
        <li wire:key="switch-{{ $b['id'] }}">
            <button type="button" wire:click="select({{ $b['id'] }})" @click="open = false"
                @class([ 'w-full flex items-center gap-3 px-3 h-11 rounded-xl text-sm text-left transition-colors'
                , 'bg-white/70 text-[#024F86] font-semibold shadow-sm'=> $brand == $b['id'],
                'text-slate-700 hover:bg-white/50' => $brand != $b['id'],
                ])>
                <span @class([ 'w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold'
                    , 'bg-[#01305A] text-white'=> $brand == $b['id'],
                    'bg-white/70 text-slate-700' => $brand != $b['id'],
                    ])>
                    {{ $initials($b['name']) }}
                </span>
                <span class="flex-1">{{ $b['name'] }}</span>
                @if ($brand == $b['id'])
                <i class="fa-solid fa-check text-xs"></i>
                @endif
            </button>
        </li>
        @endforeach
    </ul>
    @endif
</div>
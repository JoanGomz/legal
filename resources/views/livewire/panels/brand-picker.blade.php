<?php

use App\Http\Controllers\Base\BrandsController;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.guest')] class extends Component {
    public function select(int $id)
    {
        abort_unless(auth()->user()->hasRole('SuperAdmin'), 403);

        session(['brand_id' => $id]);

        // vuelve a donde intentaba entrar, o al dashboard
        return $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function with(): array
    {
        return [
            'brands' => app(BrandsController::class)->indexPaginated(1, 50, '')['data'] ?? [],
        ];
    }
}; ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-slate-100">
    <div class="w-full max-w-3xl bg-white rounded-3xl border border-slate-200 px-6 py-14 sm:px-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-blue-50 text-[#024F86] flex items-center justify-center">
            <i class="fa-solid fa-tag text-xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Hola, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-500">Elige la marca con la que vas a trabajar. Podrás cambiarla después desde la barra
            superior.</p>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
            @forelse ($brands as $b)
            <button type="button" wire:key="pick-{{ $b['id'] }}" wire:click="select({{ $b['id'] }})"
                wire:loading.attr="disabled"
                class="group flex items-center gap-4 p-5 rounded-2xl border border-slate-300 bg-white
                           hover:border-[#024F86] hover:shadow-lg hover:shadow-[#024F86]/10 transition-all disabled:opacity-60">
                <span
                    class="w-12 h-12 shrink-0 rounded-xl bg-[#01305A] text-white flex items-center justify-center font-extrabold">
                    {{ collect(explode(' ', $b['name']))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('') }}
                </span>
                <span class="flex-1">
                    <span class="block font-bold text-slate-900">{{ $b['name'] }}</span>
                    <span class="block text-sm text-slate-500">Ver métricas y usuarios</span>
                </span>
                <i
                    class="fa-solid fa-arrow-right text-slate-400 group-hover:text-[#024F86] group-hover:translate-x-0.5 transition"></i>
            </button>
            @empty
            <p class="col-span-full text-center text-slate-500 py-8">No hay marcas activas. Contacta al administrador.
            </p>
            @endforelse
        </div>
    </div>
</div>
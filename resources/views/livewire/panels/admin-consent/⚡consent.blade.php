<?php

use App\Http\Controllers\Operation\ConsetController;
use App\Traits\traitCruds;
use Livewire\Component;

new class extends Component
{
    use traitCruds;
    public function download($pdf)
    {

        try {
            if (isset($pdf)) {
                $this->js("
                setTimeout(() => {
                    const link = document.createElement('a');
                    link.href = '{$pdf}';
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }, 1000);
            ");
                $this->dispatch('hide-loading');
            }
        } catch (\Throwable $th) {
            $message = 'Ocurrio un error al crear el PDF.F';
            $this->handleException($th, $message);
        }
    }
    public function with()
    {

        $consent = app(ConsetController::class)->indexPaginated($this->page, $this->perPage, $this->search);
        return [
            'consent' => $consent,
        ];
    }
};
?>

@component('livewire.panels.admin-consent.consent-layout')
<div class="space-y-4 pb-4">

    <div class="flex justify-between items-center px-4">
        <x-input-search mode="tableSearch" placeholder="Buscar consentimiento"></x-input-search>
    </div>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">Id</th>
                    <th scope="col" class="px-6 py-3 font-medium">Codigo Consentimiento</th>
                    <th scope="col" class="px-6 py-3 font-medium">Adulto</th>
                    <th scope="col" class="px-6 py-3 font-medium">Menor</th>
                    <th scope="col" class="px-6 py-3 font-medium">Parque</th>
                    <th scope="col" class="px-6 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($consent['data'] as $item )
                <tr class="bg-neutral-primary-soft border-b  border-default">
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                        {{ $item['id']}}
                    </th>
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap relative"
                        style="left:3rem;">
                        {{ $item['code']}}
                    </th>
                    <td class="px-2 py-4">
                        <div class="relative group">
                            <!-- Vista limpia - solo lo esencial -->
                            <div class="cursor-pointer">
                                <div class="text-sm font-medium text-gray-900 md:relative" style="left:-2rem;">
                                    {{ $item ['full_name'] }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i style=" left:2rem;" class="fa-solid fa-user mr-1 md:relative"></i>

                                </div>
                            </div>
                            <!-- Tooltip con información completa -->
                            <div
                                class="absolute left-0 top-full mt-2 w-72 bg-slate-950 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                <h4 class="font-semibold text-yellow-400 mb-2">Detalles del Adulto</h4>
                                <div class="space-y-1">
                                    <div><span class="font-semibold">Parentesco:</span>
                                        {{ $item['relationship']}}
                                    </div>

                                    <div><span class="font-semibold">Tipo de documento:</span>
                                        {{ $item['document_type']}}
                                    </div>

                                    <div><span class="font-semibold">Núnero de Documento:</span>
                                        {{ $item['document_number']}}

                                    </div>
                                    <div><span class="font-semibold">Telefono:</span>
                                        {{ $item['phone']}}

                                    </div>
                                    <div><span class="font-semibold">Email:</span>
                                        {{ $item['email']}}
                                    </div>
                                </div>
                                <!-- Flecha del tooltip -->
                                <div class="absolute -top-1 left-4 w-2 h-2 bg-gray-800 transform rotate-45">
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-2 py-4 items-center">
                        <div class="relative group">

                            <div class="cursor-pointer flex items-center md:relative" style="right: 15px;">
                                <span>{{ $item['minor_full_name'] }}</span>
                                <i
                                    class="fa-solid fa-info-circle text-gray-400 ml-2 group-hover:text-blue-500 transition-colors"></i>
                            </div>
                            <!-- Tooltip con información completa -->
                            <div
                                class="absolute left-0 top-full mt-2 w-72 bg-white text-black text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 shadow-xl">
                                <h4 class="font-semibold text-center text-black mb-2">Detalles del Menor</h4>
                                <div class="space-y-1">

                                    <div><span class="font-semibold">Tipo de documento</span>
                                        {{ $item['minor_document_type'] }}
                                    </div>

                                    <div><span class="font-semibold">Número de documento:</span>
                                        {{ $item['minor_document_number'] }}

                                    </div>

                                    <div><span class="font-semibold">Fecha de nacimiento:</span>
                                        {{ $item['minor_birth_date'] }}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->park->name }}
                    </td>
                    <td class="px-2 py-4 flex gap-2 relative" style="right:-13px;">
                        <button aria-label="Descargar factura" wire:loading.attr="disabled"
                            wire:loading.class="cursor-default opacity-50" @click="window.dispatchEvent(new CustomEvent('show-loading', {
                                            detail: { message: 'Generando el consentimiento...' }
                                        }))" wire:click="download('{{ $item->url_pdf }}')">
                            <i class="fa-solid fa-download fa-xl"></i>
                        </button>
                        <a href="{{ $item->url_pdf }}" aria-label="Ver Consentimiento" target="__blank">
                            <i class="fa-regular fa-eye fa-xl"></i>
                        </a>
                    </td>
                </tr>
                @empty

                <h3 class="text-lg font-bold text-gray-800">No se encontraron Consentimienos</h3>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-buttons-pagination :data="$consent"></x-buttons-pagination>
</div>
@endcomponent
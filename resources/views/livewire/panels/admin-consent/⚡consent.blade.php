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

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th scope="col" class="px-4 py-3 font-bold">ID</th>
                    <th scope="col" class="px-4 py-3 font-bold">Código</th>
                    <th scope="col" class="px-4 py-3 font-bold">Adulto Responsable</th>
                    <th scope="col" class="px-4 py-3 font-bold">Menor</th>
                    <th scope="col" class="px-4 py-3 font-bold">Parque</th>
                    <th scope="col" class="px-4 py-3 font-bold">Fecha del Evento</th>
                    <th scope="col" class="px-4 py-3 font-bold text-center">Docs</th>
                    <th scope="col" class="px-4 py-3 font-bold text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($consent['data'] as $item)
                <tr class="bg-white hover:bg-gray-50 transition-colors">
                    <!-- ID -->
                    <td class="px-4 py-4 font-medium text-gray-900">
                        #{{ $item['id'] }}
                    </td>

                    <!-- Código -->
                    <td class="px-4 py-4">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ $item['code'] }}
                        </span>
                    </td>

                    <!-- Adulto con Tooltip -->
                    <td class="px-4 py-4">
                        <div class="relative group cursor-help">
                            <div class="flex items-center">
                                <i class="fa-solid fa-circle-user text-gray-400 mr-2"></i>
                                <span class="text-gray-900 font-medium border-b border-dotted border-gray-400">
                                    {{ $item['full_name'] }}
                                </span>
                            </div>

                            <!-- Tooltip -->
                            <div
                                class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 bottom-full mb-2 left-0 w-64 p-3 bg-gray-900 text-white rounded-lg shadow-xl text-xs">
                                <p class="font-bold text-blue-400 mb-1 border-b border-gray-700 pb-1">Detalles del
                                    Adulto</p>
                                <ul class="space-y-1 mt-2">
                                    <li><span class="text-gray-400">Parentesco:</span> {{ $item['relationship'] }}</li>
                                    <li><span class="text-gray-400">Doc:</span> {{ $item['document_type'] }}
                                        {{ $item['document_number'] }}
                                    </li>
                                    <li><span class="text-gray-400">Tel:</span> {{ $item['phone'] }}</li>
                                    <li><span class="text-gray-400">Email:</span> {{ $item['email'] }}</li>
                                </ul>
                                <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-4"></div>
                            </div>
                        </div>
                    </td>

                    <!-- Menor con Tooltip -->
                    <td class="px-4 py-4">
                        <div class="relative group cursor-help">
                            <div class="flex items-center">
                                <i class="fa-solid fa-child text-gray-400 mr-2"></i>
                                <span
                                    class="border-b border-dotted border-gray-400">{{ $item['minor_full_name'] }}</span>
                            </div>

                            <div
                                class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 bottom-full mb-2 left-0 w-64 p-3 bg-white text-gray-800 border border-gray-200 rounded-lg shadow-xl text-xs">
                                <p class="font-bold text-indigo-600 mb-1 border-b border-gray-100 pb-1">Detalles del
                                    Menor</p>
                                <ul class="space-y-1 mt-2">
                                    <li><span class="text-gray-500">Doc:</span> {{ $item['minor_document_type'] }}
                                        {{ $item['minor_document_number'] }}
                                    </li>
                                    <li><span class="text-gray-500">Nacimiento:</span> {{ $item['minor_birth_date'] }}
                                    </li>
                                </ul>
                                <div
                                    class="absolute h-2 w-2 bg-white border-r border-b border-gray-200 rotate-45 -bottom-1 left-4">
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Parque -->
                    <td class="px-4 py-4">
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                            {{ $item->park->name }}
                        </span>
                    </td>

                    <td class="px-4 py-4 font-medium text-gray-900">
                        {{ $item['event_date'] ?? '--' }}
                    </td>

                    <!-- Doc Evento -->
                    <td class="px-4 py-4 text-center">
                        <button wire:click="download('{{ $item->url_file }}')"
                            class="text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-file-pdf fa-lg"></i>
                        </button>
                    </td>

                    <!-- Acciones -->
                    <td class="px-4 py-4 text-center">
                        <div class="flex justify-center gap-3">
                            <button wire:click="download('{{ $item->url_pdf }}')"
                                class="text-gray-600 hover:text-green-600 transition-colors" title="Descargar Factura">
                                <i class="fa-solid fa-download"></i>
                            </button>
                            <a href="{{ $item->url_pdf }}" target="_blank"
                                class="text-gray-600 hover:text-blue-600 transition-colors" title="Ver Online">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <i class="fa-solid fa-folder-open fa-2x mb-3 block text-gray-300"></i>
                        No se encontraron consentimientos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-buttons-pagination :data="$consent"></x-buttons-pagination>
</div>
@endcomponent
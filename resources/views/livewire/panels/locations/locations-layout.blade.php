<div class="space-y-4 py-2 px-2">
    <!-- Encabezado de página -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ __('Gestión de Sedes') }}</h1>
            <p class="text-sm text-gray-500 ">Gestiona las sedes </p>
        </div>
    </div>

    <!-- Tarjeta principal para navegación de pestañas -->
    <div class="bg-white rounded-xl shadow-md overflow-auto ">
        <!-- Navegación de pestañas -->
        <div class="flex gap-8 py-2 px-4">
            <a href="{{ route('branches') }}" wire:navigate @class([ 'py-4 text-sm font-bold border-b-2 transition-all'
                , 'border-brand-purple text-brand-purple'=>
                request()->routeIs(
                'branches'),
                'border-transparent text-gray-400' => !request()->routeIs('branches'),
                ])>
                Parques
            </a>
            @can('atraccion')
            <a href="{{ route('atracctions') }}" wire:navigate
                @class([ 'py-4 text-sm font-bold border-b-2 transition-all' , 'border-brand-purple text-brand-purple'>
                request()->routeIs(
                'atracctions'),
                'border-transparent text-gray-400' => !request()->routeIs('atracctions'),
                ])>
                Atracciones
            </a>
            @endcan
        </div>

        <!-- Contenido de las pestañas -->
        <div class="px-4">
            {{ $slot }}
        </div>
    </div>
    <!--MODAL DE CONFIRMACIÓN-->
    @include('components.confirmation-modal')
</div>
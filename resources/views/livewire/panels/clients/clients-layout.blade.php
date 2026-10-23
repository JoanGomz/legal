<div class="space-y-4 py-2 px-2">
    <!-- Encabezado de página -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ __('Gestión de Clientes') }}</h1>
            <p class="text-sm text-gray-500 ">Gestiona los clientes de tú Parque</p>
        </div>
    </div>

    <!-- Tarjeta principal para navegación de pestañas -->
    <div class="bg-white rounded-xl shadow-md overflow-auto ">

        <!-- Contenido de las pestañas -->
        <div class="px-4">
            {{ $slot }}
        </div>
    </div>
    <!--MODAL DE CONFIRMACIÓN-->
    @include('components.confirmation-modal')
    @include('components.loading-notification')
</div>
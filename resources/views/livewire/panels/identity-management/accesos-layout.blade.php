<div class="space-y-4 py-2 px-2">
    <!-- Encabezado de página -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden p-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ __('Gestión de usuarios') }}</h1>
            <p class="text-sm text-gray-500 ">Gestiona los datos de tus usuarios, roles y permisos</p>
        </div>
    </div>

    <!-- Tarjeta principal para navegación de pestañas -->
    <div class="bg-white  rounded-xl shadow-md overflow-auto ">
        <!-- Navegación de pestañas -->
        <div>
            <div class="flex gap-8 py-2 px-4">
                <a href="{{ route('access.users') }}" wire:navigate @class([
                    'py-4 text-sm font-bold border-b-2 transition-all',
                    'border-brand-purple text-brand-purple' => request()->routeIs(
                        'access.users'),
                    'border-transparent text-gray-400' => !request()->routeIs('access.users'),
                ])>
                    Usuarios
                </a>
                <a href="{{ route('access.roles') }}" wire:navigate @class([
                    'py-4 text-sm font-bold border-b-2 transition-all',
                    'border-brand-purple text-brand-purple' => request()->routeIs(
                        'access.roles'),
                    'border-transparent text-gray-400' => !request()->routeIs('access.roles'),
                ])>
                    Roles
                </a>
                <a href="{{ route('access.permissions') }}" wire:navigate @class([
                    'py-4 text-sm font-bold border-b-2 transition-all',
                    'border-brand-purple text-brand-purple' => request()->routeIs(
                        'access.permissions'),
                    'border-transparent text-gray-400' => !request()->routeIs(
                        'access.permissions'),
                ])>
                    Permisos
                </a>
            </div>
        </div>
        <!-- Contenido de las pestañas -->
        <div class="px-4">
            {{ $slot }}
        </div>
    </div>
    <!--MODAL DE CONFIRMACIÓN-->
    @include('components.confirmation-modal')
</div>

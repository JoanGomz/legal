<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="spoon-ico.ico" type="spoon-ico.ico">
    <title>{{ config('app.name', 'Legal') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">
        <x-sidebar />
        <div :class="sidebarOpen ? 'md:ml-64' : 'md:ml-[72px]'"
            class="flex flex-col transition-all duration-300 min-h-screen">
            <livewire:layout.navigation />
            <main class="flex-1 p-4">
                {{ $slot }}
                @livewireScripts
            </main>
        </div>
    </div>
    @include('components.loading-notification')
    @include('components.notification')
</body>

</html>
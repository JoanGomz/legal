<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans h-full bg-slate-950 antialiased">
    <div class="min-h-screen bg-gradient-button flex flex-col justify-center items-center ">
        <div class="absolute bottom-4 right-4 opacity-30 z-50">

        </div>

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
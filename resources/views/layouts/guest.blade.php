<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="spoon-ico.ico" type="spoon-ico.ico">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .wave-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 180px;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }

        .wave-container {
            display: flex;
            position: absolute;
            bottom: 0;
            width: 100%;
            /* 1440px * 2 */
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* ONDA FRONTAL: Hacia la izquierda */
        .wave-container.front {
            left: 0;
            animation: wave-left 20s linear infinite !important;
        }

        /* ONDA TRASERA: Hacia la derecha */
        .wave-container.back {
            opacity: 0.4;
            bottom: 50px;
            left: -100%;
            /* Empezamos desplazados para el bucle derecho */
            animation: wave-right 15s linear infinite !important;
        }

        @keyframes wave-left {
            0% {
                transform: translate3d(0, 0, 0);
            }

            100% {
                transform: translate3d(-100%, 0, 0);
            }
        }

        @keyframes wave-right {
            0% {
                transform: translate3d(0, 0, 0);
            }

            100% {
                transform: translate3d(100%, 0, 0);
            }
        }

        .wave-container svg {
            width: 100%;
            height: 100%;
            display: block;
            flex-shrink: 0;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans h-full bg-slate-950 antialiased">
    <div class="min-h-screen bg-gradient-button flex flex-col justify-center items-center ">
        <div class="absolute bottom-4 right-4 opacity-30 z-50">

        </div>

        <div class="w-full">
            {{ $slot }}
        </div>
        @livewireScripts
    </div>
    @include('components.loading-notification')
    @include('components.notification')
</body>

</html>
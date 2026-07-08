<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consentimiento Informado</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .page-bg {
            background-image: url("https://s3.amazonaws.com/pdf-nocard/firmware/updates/TARJETA%20FORMULARIO.png");
            background-size: 100% 100%;
            background-repeat: no-repeat;
            width: 216mm;
            height: 279mm;
            position: relative;
        }

        .data-field-centered-bold {
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            font-size: 24px;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .data-field-centered {
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            font-size: 24px;
            color: #fff;
            text-transform: uppercase;
        }

        .data-field-title {
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            font-size: 24px;
            color: #1b0069;
            font-weight: bold;
            text-transform: uppercase;
        }

        .data-field-left {
            position: absolute;
            font-size: 22px;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .data-event {
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            font-size: 22px;
            color: #fff;
            text-transform: uppercase;
        }

        #arcade {
            top: 11%;
        }

        #full_name {
            top: 31.5%;
        }

        #document_number {
            top: 40.5%;
        }

        #phone_number {
            top: 49%;
        }

        #code {
            top: 62%;
            font-size: 28px;
        }

        #event_date {
            top: 75.5%;
        }

        /* NUEVO CONTENEDOR PARA LOS MENORES */
        .minors-container {
            position: absolute;
            top: 75%;
            /* Altura de inicio ideal dentro del recuadro azul */
            width: 80%;
            /* Centrado horizontal */
            left: 10%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 6px;
            /* Espaciado corto entre filas */
        }

        .minor-row {
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="page-bg">
        <!-- Arcade -->
        <div id="arcade" class="data-field-title">
            {{ $arcade->nombre }}
        </div>

        <!-- Parent Info -->
        <div class="data-field-centered" style="top: 23.9%;">
            {{ $registration->created_at->format('d/m/Y H:i') }}
        </div>

        <div class="data-field-centered-bold" style="top: 28.5%;">
            Nombre y Apellido:
        </div>

        <div id="full_name" class="data-field-centered">
            {{ $registration->full_name }}
        </div>

        <div class="data-field-centered-bold" style="top: 37.5%;">
            Tipo de documento y Número:
        </div>

        <div id="document_number" class="data-field-centered">
            {{$registration->document_type}}. {{ $registration->document_number }}
        </div>

        <div class="data-field-centered-bold" style="top: 46%;">
            Número de Celular:
        </div>

        <div id="phone_number" class="data-field-centered">
            {{ $registration->phone }}
        </div>

        <!-- Code registration -->
        <div id="code" class="data-field-centered-bold">
            {{ $code }}
        </div>

        <!-- Minor Info o Event Date -->
        @if($registration->url_file && $registration->event_date)

        <div id="event_date" class="data-event">
            Estara sujeto al archivo del formulario cargado anteriormente,
            con fecha de evento:
        </div>

        <div class="data-field-centered-bold" style="top: 82%;">
            {{ $registration->event_date }}
        </div>

        @else
        <!-- Contenedor dinámico que evita que se encimen los niños -->
        <div class="minors-container">
            @php
            $totalChildren = count($data['childrens']);
            // Si hay muchos niños achicamos la fuente un poco para que entren todos sin salirse
            $fontSize = '22px';
            if ($totalChildren >= 5) { $fontSize = '17px'; }
            elseif ($totalChildren >= 4) { $fontSize = '19px'; }
            elseif ($totalChildren >= 3) { $fontSize = '20px'; }
            @endphp

            @foreach($data['childrens'] as $child)
            <div class="minor-row" style="font-size: {{ $fontSize }};">
                {{ $child['minor_full_name'] }} - {{ $child['minor_birth_date'] }}
            </div>
            @endforeach
        </div>
        @endif

    </div>

</body>

</html>
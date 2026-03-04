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
            /* Tamaño carta */
            height: 279mm;
            position: relative;
        }

        /* 3. Clase para centrado automático */
        .data-field-centered-bold {
            position: absolute;
            width: 100%;
            /* Ocupa todo el ancho de la hoja */
            left: 0;
            text-align: center;
            /* Centra el texto respecto al ancho */
            font-size: 24px;
            /* Ajusté un poco el tamaño para que nombres largos no se amontonen */
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .data-field-centered {
            position: absolute;
            width: 100%;
            /* Ocupa todo el ancho de la hoja */
            left: 0;
            text-align: center;
            /* Centra el texto respecto al ancho */
            font-size: 24px;
            /* Ajusté un poco el tamaño para que nombres largos no se amontonen */
            color: #fff;
            text-transform: uppercase;
        }

        .data-field-title {
            position: absolute;
            width: 100%;
            /* Ocupa todo el ancho de la hoja */
            left: 0;
            text-align: center;
            /* Centra el texto respecto al ancho */
            font-size: 24px;
            /* Ajusté un poco el tamaño para que nombres largos no se amontonen */
            color: #1b0069;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* 4. Clase para campos alineados a la izquierda (como el footer) */
        .data-field-left {
            position: absolute;
            font-size: 22px;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* 5. Coordenadas Verticales (TOP) */
        /* El 'left' ya no es necesario en los centrados porque usamos width 100% */

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

        #minor_name {
            top: 78.5%;
        }
    </style>
</head>

<body>

    <div class="page-bg">

        <div id="arcade" class="data-field-title">
            {{ $arcade->nombre }}
        </div>

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

        <div id="code" class="data-field-centered-bold">
            {{ $code }}
        </div>

        <div class="data-field-centered-bold" style="top: 75.5%;">
            Nombre y Apellido:
        </div>

        <div id="minor_name" class="data-field-centered">
            {{ $registration->minor_full_name }}
        </div>

        <div class="data-field-centered" style="top: 82%;">
            {{$registration->minor_document_type}}. {{ $registration->minor_document_number }}
        </div>

    </div>

</body>

</html>
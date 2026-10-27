<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consentimiento Informado</title>

    <style>
        @page {
            size: letter;
            margin: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #29304a;
            background: #ffffff;
        }

        .top-line {
            height: 7px;
            background-color: #6c4ce8;
        }

        .header {
            background-color: #241568;
            color: #ffffff;
            padding: 25px 22px 22px;
            text-align: center;
            border-radius: 0 0 18px 18px;
            position: relative;
        }

        .header-logo {
            position: absolute;
            top: 52px;
            left: 22px;
            width: 95px;
        }

        .brand {
            color: #bdb0ff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 23px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-subtitle {
            margin-top: 8px;
            color: #e5e0ff;
            font-size: 11px;
        }

        .header-date {
            margin-top: 17px;
            padding: 8px 12px;
            background-color: #382780;
            border-radius: 14px;
            color: #ffffff;
            font-size: 10px;
        }

        .intro {
            margin: 20px 0 18px;
            padding: 12px 15px;
            background-color: #f2efff;
            border-left: 4px solid #7958ed;
            border-radius: 4px;
            color: #51417f;
            line-height: 1.5;
            font-size: 11px;
        }

        .section-heading {
            margin: 20px 0 12px;
            color: #241568;
            font-size: 14px;
            font-weight: bold;
        }

        .section-number {
            display: inline-block;
            background-color: #6c4ce8;
            color: #ffffff;
            padding: 5px 8px;
            border-radius: 6px;
            margin-right: 7px;
            font-size: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 7px;
            margin: -7px;
        }

        .info-table td {
            width: 50%;
            padding: 12px;
            background-color: #f7f8fc;
            border: 1px solid #e9e8f4;
            border-radius: 8px;
            vertical-align: top;
        }

        .field-label {
            color: #7b7f96;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 7px;
            letter-spacing: 0.4px;
        }

        .field-value {
            color: #292047;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .phone-card {
            margin-top: 7px;
            padding: 12px;
            background-color: #eef9fa;
            border: 1px solid #d6f0f0;
            border-radius: 8px;
        }

        .phone-card .field-label {
            color: #39858b;
        }

        .phone-card .field-value {
            color: #17666c;
        }

        .code-card {
            margin-top: 20px;
            background-color: #241568;
            border-radius: 12px;
            padding: 19px 12px;
            text-align: center;
        }

        .code-kicker {
            color: #c9bdff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .code-value {
            color: #ffffff;
            font-size: 27px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-top: 9px;
        }

        .code-note {
            color: #ded8ff;
            font-size: 9px;
            margin-top: 7px;
        }

        .children-intro {
            color: #777b91;
            font-size: 10px;
            margin: -3px 0 10px;
        }

        .children-table {
            width: 100%;
            border-collapse: collapse;
        }

        .children-table th {
            background-color: #eeeaff;
            color: #4a368d;
            padding: 10px 9px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 2px solid #d9d0ff;
        }

        .children-table td {
            padding: 11px 9px;
            border-bottom: 1px solid #e9e8f1;
            font-size: 11px;
            line-height: 1.4;
        }

        .children-table tr:nth-child(even) td {
            background-color: #faf9ff;
        }

        .child-name {
            color: #292047;
            font-weight: bold;
        }

        .child-date {
            color: #656b83;
        }

        .event-card {
            margin-top: 12px;
            padding: 17px;
            background-color: #eef9fa;
            border: 1px solid #d2eeee;
            border-radius: 9px;
            line-height: 1.7;
            color: #285b65;
        }

        .event-date {
            color: #17666c;
            font-size: 15px;
            font-weight: bold;
            margin-top: 8px;
        }

        .closing {
            margin-top: 23px;
            padding: 13px;
            background-color: #f7f6fc;
            border-radius: 8px;
            text-align: center;
            color: #68627d;
            font-size: 10px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e8e3ff;
            text-align: center;
            color: #8b8ba1;
            font-size: 9px;
        }

        .footer strong {
            color: #6c4ce8;
        }
    </style>
</head>

<body>

    <div class="top-line"></div>

    @php
    $logoUrl = 'https://bucketmarketingstarpark.s3.amazonaws.com/chat-files/1793051593_logo-monstropolis.png';
    $logoBase64 = null;
    try {
    $logoContent = \Illuminate\Support\Facades\Http::timeout(5)->get($logoUrl);
    if ($logoContent->successful()) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent->body());
    }
    } catch (\Throwable $e) {
    $logoBase64 = null;
    }
    @endphp

    <div class="header">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="header-logo" alt="Monstrópolis">
        @endif

        <div class="brand">EUREMA</div>

        <h1>Consentimiento informado</h1>

        <div class="header-subtitle">
            Registro de participante
        </div>

        <div class="header-date">
            Fecha de registro:
            {{ $registration->created_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="intro">
        <strong>¡Bienvenido!</strong>
        A continuación encontrarás la información asociada
        al registro del participante y su código de identificación.
        Te recomendamos conservar este documento.
    </div>

    <div class="section-heading">
        <span class="section-number">01</span>
        Información del titular
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="field-label">Nombre y apellido</div>
                <div class="field-value">
                    {{ $registration->full_name }}
                </div>
            </td>

            <td>
                <div class="field-label">Documento de identidad</div>
                <div class="field-value">
                    {{ $registration->document_type }}
                    {{ $registration->document_number }}
                </div>
            </td>
        </tr>
    </table>

    <div class="phone-card">
        <div class="field-label">Número de celular</div>
        <div class="field-value">
            {{ $registration->phone }}
        </div>
    </div>

    <div class="code-card">
        <div class="code-kicker">Tu código de registro</div>

        <div class="code-value">
            {{ $code }}
        </div>

        <div class="code-note">
            Código de identificación del registro
        </div>
    </div>

    @if($registration->url_file && $registration->event_date)

    <div class="section-heading">
        <span class="section-number">02</span>
        Información del evento
    </div>

    <div class="event-card">
        El registro estará sujeto al archivo del formulario
        cargado anteriormente.

        <div class="field-label" style="margin-top: 13px;">
            Fecha del evento
        </div>

        <div class="event-date">
            {{ $registration->event_date }}
        </div>
    </div>

    @else

    @php
    $childrens = $data['childrens'] ?? [];
    @endphp

    @if(count($childrens) > 0)

    <div class="section-heading">
        <span class="section-number">02</span>
        Información de los menores
    </div>

    <div class="children-intro">
        Listado de menores asociados a este registro.
    </div>

    <table class="children-table">
        <thead>
            <tr>
                <th style="width: 65%;">Nombre completo</th>
                <th style="width: 35%;">Fecha de nacimiento</th>
            </tr>
        </thead>

        <tbody>
            @foreach($childrens as $child)
            <tr>
                <td class="child-name">
                    {{ $child['minor_full_name'] ?? 'Sin nombre' }}
                </td>

                <td class="child-date">
                    {{ $child['minor_birth_date'] ?? 'Sin fecha' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @endif

    @endif

    <div class="closing">
        Este documento corresponde a la información registrada
        para el participante.

        <br>

        <strong>Gracias por ser parte de la experiencia.</strong>
    </div>

    <div class="footer">
        <strong>EUREMA</strong>
        &nbsp; | &nbsp;
        Documento generado electrónicamente
    </div>

</body>

</html>
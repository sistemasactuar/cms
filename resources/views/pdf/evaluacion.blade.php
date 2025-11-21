<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
    }

    .logo {
        width: 160px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 11px;
    }

    .no-border td {
        border: none;
    }

    .titulo {
        background: #e5e5e5;
        text-align: center;
        font-weight: bold;
    }

    .seccion {
        background: #f0f0f0;
        font-weight: bold;
    }

    .firma-box {
        height: 90px;
        border: 1px solid #000;
        margin-top: 25px;
        text-align: center;
        padding-top: 50px;
        font-size: 11px;
    }

</style>
</head>

<body>

<table class="no-border">
    <tr>
        <td><img src="{{ public_path('logo_actuar.png') }}" class="logo"></td>
        <td style="text-align:center; font-weight:bold;">
            CORPORACIÓN ACTUAR FAMIEMPRESAS<br>
            <span style="font-size:12px;">EVALUACIÓN DE PROVEEDORES INSTITUCIONALES</span>
        </td>
        <td style="font-size:11px;">
            <b>CÓDIGO:</b> GCFR005 <br>
            <b>VERSIÓN:</b> 2 <br>
            <b>FECHA:</b> 23/10/2018
        </td>
    </tr>
</table>

<br>

<table>
    <tr>
        <td><b>Razón Social:</b> {{ $evaluacion->proveedor->nombre }}</td>
        <td><b>Fecha Control:</b> {{ $evaluacion->fecha->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td><b>Contacto:</b> {{ $evaluacion->proveedor->contacto }}</td>
        <td><b>Fecha Próximo Control:</b> — </td>
    </tr>
    <tr>
        <td colspan="2"><b>Servicio:</b> {{ $evaluacion->proveedor->servicio }}</td>
    </tr>
</table>

<br>

<table>
    <tr class="titulo">
        <td style="width:30px;">No.</td>
        <td>ITEM A EVALUAR</td>
        <td style="width:50px;">Calif.</td>
    </tr>

    @php
        $preguntas = [
            1 => '¿Tiene precios competitivos para su servicio?',
            2 => '¿Sus tiempos de respuesta se adecuan a nuestras necesidades?',
            3 => '¿Suministra información técnica apropiada?',
            4 => '¿Brinda la asesoría requerida?',
            5 => '¿Conoce bien su servicio?',
            6 => '¿Asiste a reuniones solicitadas específicamente?',
            7 => '¿Plantea innovaciones y mejoras en su servicio periódicamente?',
            8 => '¿Es oportuno en la solución de quejas y reclamos?',
            9 => '¿Ofrece garantía de productos y/o servicios?',
            10 => '¿Es amable en la atención del servicio?',
            11 => '¿La calidad del servicio cumple con lo requerido?',
        ];
        $labels = ['na' => 'N/A', 0 => '0', 1 => '1', 2 => '2'];
    @endphp

    @foreach($preguntas as $num => $texto)
    <tr>
        <td>{{ $num }}</td>
        <td>{{ $texto }}</td>
        <td style="text-align:center">{{ $labels[$evaluacion["pregunta_$num"]] ?? '' }}</td>
    </tr>
    @endforeach
</table>

<br>

<table>
    <tr class="seccion">
        <td colspan="2">SISTEMA DE PUNTUACIÓN</td>
        <td>PUNTOS ADICIONALES</td>
    </tr>
    <tr>
        <td>N/A</td>
        <td>No aplicable</td>
        <td rowspan="4"></td>
    </tr>
    <tr>
        <td>0</td>
        <td>No cumple</td>
    </tr>
    <tr>
        <td>1</td>
        <td>Cumple parcialmente</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Cumple</td>
    </tr>
</table>

<br>

<table>
    <tr>
        <td><b>TOTAL PUNTOS OBTENIDOS:</b></td>
        <td>{{ $evaluacion->puntos_obtenidos }}</td>
        <td rowspan="3" style="text-align:center;">
            <b>{{ $evaluacion->calificacion }}%</b>
        </td>
    </tr>
    <tr>
        <td><b>TOTAL PUNTOS POSIBLES:</b></td>
        <td>{{ $evaluacion->puntos_posibles }}</td>
    </tr>
    <tr>
        <td><b>CANTIDAD DE PREGUNTAS APLICABLES:</b></td>
        <td>{{ $evaluacion->puntos_posibles / 2 }}</td>
    </tr>
</table>

<br><br>

<table>
    <tr>
        <td><b>CLASIFICACIÓN FINAL:</b> {{ $evaluacion->clasificacion }}</td>
    </tr>
    <tr>
        <td><b>Observaciones:</b><br>{{ $evaluacion->observaciones }}</td>
    </tr>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td style="text-align:center;">
            <div class="firma-box">
                @if($evaluacion->firma)
                    <img src="{{ public_path('firmas/'.$evaluacion->firma) }}" style="width:200px;">
                @endif
            </div>
            <br>Responsable
        </td>

        <td style="text-align:center;">
            ____________________________ <br>
            VoBo Dirección Administrativa
        </td>
    </tr>
</table>

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha Tecnica del Equipo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }

        th {
            text-align: left;
        }

        .titulo-seccion {
            background: #ddd;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }

        .encabezado td,
        .encabezado th {
            border: 1px solid #000;
        }

        .small {
            font-size: 10px;
        }

        .logo {
            width: 80px;
            height: auto;
        }
    </style>
</head>
<body>
    @php
        $siNo = [0 => 'SI', 1 => 'NO'];
        $tipoDisco = [0 => 'HDD', 1 => 'SSD', 2 => 'M2', 3 => 'N/A'];
        $tipoVigilancia = [0 => 'DVR', 1 => 'NVR'];

        $logoBase64 = null;
        $logoPath = public_path('images/LOGO-03.png');
        if (is_file($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $exploradores = trim(implode(' ', array_filter([
            $activo->explorador1,
            $activo->explorador2,
            $activo->explorador3,
        ])));
    @endphp

    <table class="encabezado">
        <tr>
            <td rowspan="3" style="width:15%; text-align:center;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="Logo Actuar">
                @endif
            </td>
            <td rowspan="3" style="width:55%; text-align:center;">
                <strong>CORPORACION ACTUAR FAMIEMPRESAS</strong><br>
                <span class="small">CREDITO PARA LA MICROEMPRESA</span><br><br>
                <b>DOCUMENTO:</b> FICHA TECNICA DE EQUIPOS DE COMPUTO<br>
                <b>PROCESO:</b> SISTEMAS Y GESTION DE LA INFORMACION
            </td>
            <th style="width:15%;">CODIGO:</th>
            <td style="width:15%;">SFR010</td>
        </tr>
        <tr>
            <th>VERSION:</th>
            <td>1</td>
        </tr>
        <tr>
            <th>FECHA:</th>
            <td>23/11/2018</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th colspan="4" class="titulo-seccion">DATOS DEL EQUIPO</th>
        </tr>
        <tr>
            <th>Codigo Inventario</th>
            <td>{{ $activo->codigo }}</td>
            <th>Tipo</th>
            <td>{{ $activo->tipoActivo?->tipo }}</td>
        </tr>
        <tr>
            <th>Marca</th>
            <td>{{ $activo->marca }}</td>
            <th>Modelo</th>
            <td>{{ $activo->modelo }}</td>
        </tr>
        <tr>
            <th>Serie</th>
            <td>{{ $activo->serie }}</td>
            <th>Condicion</th>
            <td>{{ $activo->condicion }}</td>
        </tr>
        <tr>
            <th>Responsable</th>
            <td>{{ $activo->responsable }}</td>
            <th>Oficina</th>
            <td>{{ $activo->sede?->NombreSede }}</td>
        </tr>
        <tr>
            <th>Descripcion</th>
            <td colspan="3">{{ $activo->descripcion }}</td>
        </tr>
        <tr>
            <th>Valor</th>
            <td colspan="3">${{ number_format((float) $activo->valor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Observacion</th>
            <td colspan="3">{{ $activo->observacion }}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th colspan="4" class="titulo-seccion">HARDWARE</th>
        </tr>
        <tr>
            <th>Procesador</th>
            <td>{{ $activo->procesador }}</td>
            <th>RAM</th>
            <td>{{ $activo->ram }}</td>
        </tr>
        <tr>
            <th>Disco Duro 1</th>
            <td>{{ $activo->hdd1 }}</td>
            <th>Tipo Disco</th>
            <td>{{ $tipoDisco[(int) $activo->tipo_disco] ?? $activo->tipo_disco }}</td>
        </tr>
        <tr>
            <th>Disco Duro 2</th>
            <td>{{ $activo->hdd2 }}</td>
            <th>Tipo Disco 2</th>
            <td>{{ $tipoDisco[(int) $activo->tipo_disco2] ?? $activo->tipo_disco2 }}</td>
        </tr>
        <tr>
            <th>Fuente</th>
            <td>{{ $activo->fuente }}</td>
            <th>Unidad CD</th>
            <td>{{ $siNo[(int) $activo->unidad_cd] ?? $activo->unidad_cd }}</td>
        </tr>
        <tr>
            <th>Teclado</th>
            <td>{{ $activo->teclado }}</td>
            <th>Mouse</th>
            <td>{{ $activo->mouse }}</td>
        </tr>
        <tr>
            <th>Monitor</th>
            <td>{{ $activo->pantalla }}</td>
            <th>Tamano</th>
            <td>{{ $activo->pantalla_tam }}</td>
        </tr>
        <tr>
            <th>Tarjeta Video</th>
            <td>{{ $activo->t_video }}</td>
            <th>Antivirus</th>
            <td>{{ $activo->antivirus }}</td>
        </tr>
        <tr>
            <th>UPS Capacidad</th>
            <td>{{ $activo->ups_capacidad }}</td>
            <th>Cargador</th>
            <td>{{ $activo->cargador }}</td>
        </tr>
        <tr>
            <th>Puertos Telecom</th>
            <td>{{ $activo->telecom_puertos }}</td>
            <th>POE Telecom</th>
            <td>{{ $siNo[(int) $activo->telecom_pe] ?? $activo->telecom_pe }}</td>
        </tr>
        <tr>
            <th>Tipo Vigilancia</th>
            <td>{{ $tipoVigilancia[(int) $activo->vigil_tipo] ?? $activo->vigil_tipo }}</td>
            <th>POE Vigilancia</th>
            <td>{{ $siNo[(int) $activo->vigil_poe] ?? $activo->vigil_poe }}</td>
        </tr>
        <tr>
            <th>Canales Vigilancia</th>
            <td>{{ $activo->vigil_puertos }}</td>
            <th>Capacidad Vigilancia</th>
            <td>{{ $activo->vigil_capacidad }}</td>
        </tr>
        <tr>
            <th>Rango Access Point</th>
            <td colspan="3">{{ $activo->acces_point_rango }}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th colspan="4" class="titulo-seccion">SOFTWARE</th>
        </tr>
        <tr>
            <th>Sistema Operativo</th>
            <td>{{ $activo->so }}</td>
            <th>Ofimatico</th>
            <td>{{ $activo->sof }}</td>
        </tr>
        <tr>
            <th>Compresor</th>
            <td>{{ $activo->compresor }}</td>
            <th>Adobe Reader</th>
            <td>{{ $activo->adobe }}</td>
        </tr>
        <tr>
            <th>Exploradores</th>
            <td colspan="3">{{ $exploradores }}</td>
        </tr>
        <tr>
            <th>Programas Aplicacion</th>
            <td colspan="3">{!! nl2br(e((string) $activo->prog_adicionales)) !!}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th colspan="4" class="titulo-seccion">CONTROL DE MANTENIMIENTO</th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Actividad</th>
            <th>Usuario</th>
        </tr>
        @forelse($activo->mantenimientos as $mantenimiento)
            <tr>
                <td>{{ $mantenimiento->fecadi?->format('Y-m-d') }}</td>
                <td>{{ $tiposMantenimiento[(int) $mantenimiento->tipo_M] ?? $mantenimiento->tipo_M }}</td>
                <td>{!! nl2br(e((string) $mantenimiento->observacion_M)) !!}</td>
                <td>{{ $mantenimiento->creador?->name ?? $mantenimiento->usuadi }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No hay mantenimientos registrados.</td>
            </tr>
        @endforelse
    </table>

    <p style="margin-top:15px; font-size:10px; text-align:center;">
        Documento generado automaticamente por el sistema de gestion de inventarios.
    </p>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha Tecnica Activo</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        th { background: #efefef; text-align: left; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="title">FICHA TECNICA DE ACTIVO</div>

    <table>
        <tr>
            <th>Codigo Inventario</th>
            <td>{{ $activo->codigo }}</td>
            <th>Tipo</th>
            <td>{{ $activo->tipoActivo?->tipo }}</td>
        </tr>
        <tr>
            <th>Descripcion</th>
            <td>{{ $activo->descripcion }}</td>
            <th>Oficina</th>
            <td>{{ $activo->sede?->NombreSede }}</td>
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
            <th>Valor</th>
            <td>${{ number_format((float) $activo->valor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Observacion</th>
            <td colspan="3">{{ $activo->observacion }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="4">Control de Mantenimiento</th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Actividad</th>
            <th>Usuario</th>
        </tr>
        @forelse($activo->mantenimientos as $m)
            <tr>
                <td>{{ $m->fecadi?->format('Y-m-d') }}</td>
                <td>{{ $tiposMantenimiento[$m->tipo_M] ?? $m->tipo_M }}</td>
                <td>{{ $m->observacion_M }}</td>
                <td>{{ $m->creador?->name }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No hay mantenimientos registrados.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>

<table>
    <thead>
        <tr>
            <th>Suc. Cliente</th>
            <th>Id. Cliente</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Clasificación</th>
            <th>Fecha Aprobación</th>
            <th>Fecha Liquidación</th>
            <th>Monto Solicitado</th>
            <th>Saldo Capital</th>
            <th>Días Vencidos</th>
            <th>Prob. Incumplimiento</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($registros as $registro)
            <tr>
                <td>{{ $registro->suc_cliente }}</td>
                <td>{{ $registro->id_cliente }}</td>
                <td>{{ $registro->nombre }}</td>
                <td>{{ $registro->estado }}</td>
                <td>{{ $registro->clasificacion }}</td>
                <td>{{ $registro->fec_aprobacion }}</td>
                <td>{{ $registro->fec_liquidacion }}</td>
                <td>{{ $registro->monto_solicitado }}</td>
                <td>{{ $registro->saldo_capital }}</td>
                <td>{{ $registro->dias_vencidos }}</td>
                <td>{{ $registro->prob_incumplimiento_sistema }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<?php

namespace App\Imports;

use App\Models\Plano4111;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Plano4111Import implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        // Omitir filas que repiten encabezados
        if (
            $rowData['cedula'] === 'Cedula' ||
            $rowData['obligacion'] === 'Obligacion'
        ) {
            return;
        }

        Plano4111::create([
            'cedula'           => $rowData['cedula'] ?? '',
            'asociado'         => $rowData['asociado'] ?? '',
            'modalidad'        => $rowData['modalidad'] ?? '',
            'calificacion'     => $rowData['calificacion'] ?? '',
            'obligacion'       => $rowData['obligacion'] ?? '',
            'telefono'         => $rowData['telefono'] ?? '',
            'celular'          => $rowData['celular'] ?? '',
            'ciudad'           => $rowData['ciudad'] ?? '',
            'saldo_capital'    => is_numeric($rowData['saldo_capital']) ? $rowData['saldo_capital'] : 0,
            'capital_vencido'  => is_numeric($rowData['capital_vencido']) ? $rowData['capital_vencido'] : 0,
            'dias_vencidos'    => is_numeric($rowData['dias_vencidos']) ? $rowData['dias_vencidos'] : 0,
            'asesor'           => $rowData['asesor'] ?? '',
        ]);
    }
}

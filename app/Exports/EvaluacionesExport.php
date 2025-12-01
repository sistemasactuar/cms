<?php

namespace App\Exports;

use App\Models\EvaluacionProveedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        // Solo evaluaciones finalizadas (bloqueado = true)
        $evaluaciones = EvaluacionProveedor::with(['proveedor', 'responsable'])
            ->where('bloqueado', true)
            ->get();

        // Calcular promedio
        $promedio = $evaluaciones->avg('calificacion');

        // Agregar fila de promedio al final
        $evaluaciones->push((object)[
            'is_summary' => true,
            'promedio' => $promedio
        ]);

        return $evaluaciones;
    }

    public function headings(): array
    {
        return [
            'Proveedor',
            'Responsable',
            'Fecha',
            'Calificación',
            'Clasificación',
        ];
    }

    public function map($evaluacion): array
    {
        if (isset($evaluacion->is_summary)) {
            return [
                '',
                '',
                'PROMEDIO GENERAL:',
                number_format($evaluacion->promedio, 2) . '%',
                ''
            ];
        }

        return [
            $evaluacion->proveedor->nombre ?? 'N/A',
            $evaluacion->responsable->nombre ?? 'N/A',
            $evaluacion->fecha ? $evaluacion->fecha->format('Y-m-d') : '',
            $evaluacion->calificacion . '%',
            $evaluacion->clasificacion,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrasladosActivosExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $records)
    {
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Fecha Cambio',
            'Activo ID',
            'Codigo Activo',
            'Descripcion Activo',
            'Usuario Anterior',
            'Usuario Nuevo',
            'Responsable Anterior (Texto)',
            'Responsable Nuevo (Texto)',
            'Area Usuario Anterior',
            'Area Usuario Nuevo',
            'Cambio Realizado Por',
            'Motivo',
        ];
    }

    public function map($record): array
    {
        return [
            $record->changed_at?->format('Y-m-d H:i:s') ?? '',
            $record->activo_fijo_id,
            $record->activo?->codigo ?? '',
            $record->activo?->descripcion ?? '',
            $record->usuarioAnterior?->name ?? '',
            $record->usuarioNuevo?->name ?? '',
            $record->responsable_anterior ?? '',
            $record->responsable_nuevo ?? '',
            $record->usuarioAnterior?->area ?? '',
            $record->usuarioNuevo?->area ?? '',
            $record->changedBy?->name ?? '',
            $record->motivo ?? '',
        ];
    }
}


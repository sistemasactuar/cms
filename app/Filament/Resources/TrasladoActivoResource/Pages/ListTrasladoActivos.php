<?php

namespace App\Filament\Resources\TrasladoActivoResource\Pages;

use App\Exports\TrasladosActivosExport;
use App\Filament\Resources\TrasladoActivoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListTrasladoActivos extends ListRecords
{
    protected static string $resource = TrasladoActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarExcel')
                ->label('Descargar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($livewire) {
                    $records = $livewire
                        ->getFilteredTableQuery()
                        ->with(['activo', 'usuarioAnterior', 'usuarioNuevo', 'changedBy'])
                        ->get();

                    return Excel::download(
                        new TrasladosActivosExport($records),
                        'traslados_activos_' . now()->format('Ymd_His') . '.xlsx'
                    );
                }),
        ];
    }
}


<?php

namespace App\Filament\Resources\EvaluacionProveedorResource\Pages;

use App\Filament\Resources\EvaluacionProveedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluacionProveedors extends ListRecords
{
    protected static string $resource = EvaluacionProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar')
                ->label('Exportar Consolidado')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($livewire) {
                    $records = $livewire->getFilteredTableQuery()
                        ->where('bloqueado', true)
                        ->get();

                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EvaluacionesExport($records), 'evaluaciones_consolidado.xlsx');
                }),
            Actions\CreateAction::make(),
        ];
    }
}

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
                ->action(fn() => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EvaluacionesExport, 'evaluaciones_consolidado.xlsx')),
            Actions\CreateAction::make(),
        ];
    }
}

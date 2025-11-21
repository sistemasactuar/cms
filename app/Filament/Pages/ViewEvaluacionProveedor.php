<?php

namespace App\Filament\Resources\EvaluacionProveedorResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\EvaluacionProveedorResource;

class ViewEvaluacionProveedor extends ViewRecord
{
    protected static string $resource = EvaluacionProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('evaluacion.pdf', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }
}

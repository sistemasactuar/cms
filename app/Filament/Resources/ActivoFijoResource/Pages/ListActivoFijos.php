<?php

namespace App\Filament\Resources\ActivoFijoResource\Pages;

use App\Filament\Resources\ActivoFijoResource;
use App\Services\ActivoFijoSqlImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListActivoFijos extends ListRecords
{
    protected static string $resource = ActivoFijoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importarLegacySql')
                ->label('Importar SQL Legacy')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    Forms\Components\FileUpload::make('archivo_sql')
                        ->label('Archivo SQL de activos y mantenimientos')
                        ->acceptedFileTypes([
                            'application/sql',
                            'text/plain',
                            'application/octet-stream',
                        ])
                        ->disk('local')
                        ->directory('importaciones/activos')
                        ->visibility('private')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = $data['archivo_sql'] ?? null;
                    if (!$path || !Storage::disk('local')->exists($path)) {
                        Notification::make()
                            ->title('Archivo no encontrado')
                            ->body('Verifica el archivo y vuelve a intentarlo.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $result = app(ActivoFijoSqlImportService::class)->import(
                            Storage::disk('local')->path($path)
                        );
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Error al importar')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                        return;
                    }

                    Notification::make()
                        ->title('Importacion finalizada')
                        ->body(
                            "Activos -> Procesados: {$result['procesados']}, Nuevos: {$result['creados']}, " .
                            "Actualizados: {$result['actualizados']}, Ignorados: {$result['ignorados']}, " .
                            "Saltados: {$result['saltados']}. " .
                            "Mantenimientos -> Procesados: {$result['mantenimientos_procesados']}, " .
                            "Nuevos: {$result['mantenimientos_creados']}, " .
                            "Actualizados: {$result['mantenimientos_actualizados']}, " .
                            "Ignorados: {$result['mantenimientos_ignorados']}, " .
                            "Saltados: {$result['mantenimientos_saltados']}, " .
                            "Sin equipo: {$result['mantenimientos_sin_equipo']}."
                        )
                        ->success()
                        ->send();
                }),
            Actions\Action::make('mantenimientos')
                ->label('Listado Mantenimientos')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(fn(): string => \App\Filament\Resources\ActivoMantenimientoResource::getUrl('index')),
        ];
    }
}

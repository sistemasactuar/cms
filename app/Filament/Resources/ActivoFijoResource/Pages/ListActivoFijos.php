<?php

namespace App\Filament\Resources\ActivoFijoResource\Pages;

use App\Filament\Resources\ActivoFijoResource;
use App\Filament\Resources\ActivoFijoResource\Widgets\ActivoFijoStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivoFijos extends ListRecords
{
    protected static string $resource = ActivoFijoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ActivoFijoStatsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('mantenimientos')
                ->label('Listado Mantenimientos')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(fn(): string => \App\Filament\Resources\ActivoMantenimientoResource::getUrl('index')),
        ];
    }
}

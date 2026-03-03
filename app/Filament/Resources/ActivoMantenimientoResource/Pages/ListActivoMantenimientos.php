<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Pages;

use App\Filament\Resources\ActivoMantenimientoResource;
use App\Filament\Resources\ActivoMantenimientoResource\Widgets\ActivoMantenimientoStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivoMantenimientos extends ListRecords
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ActivoMantenimientoStatsOverview::class,
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
        ];
    }
}

<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Pages;

use App\Filament\Resources\ActivoMantenimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivoMantenimientos extends ListRecords
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

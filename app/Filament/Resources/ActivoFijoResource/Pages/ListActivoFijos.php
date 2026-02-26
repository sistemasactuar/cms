<?php

namespace App\Filament\Resources\ActivoFijoResource\Pages;

use App\Filament\Resources\ActivoFijoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivoFijos extends ListRecords
{
    protected static string $resource = ActivoFijoResource::class;

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

<?php

namespace App\Filament\Resources\ActividadesResource\Pages;

use App\Filament\Resources\ActividadesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActividades extends ListRecords
{
    protected static string $resource = ActividadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

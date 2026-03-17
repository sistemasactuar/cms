<?php

namespace App\Filament\Resources\AportanteResource\Pages;

use App\Filament\Resources\AportanteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAportantes extends ListRecords
{
    protected static string $resource = AportanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

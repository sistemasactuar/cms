<?php

namespace App\Filament\Resources\PlanoMoraResource\Pages;

use App\Filament\Resources\PlanoMoraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanoMoras extends ListRecords
{
    protected static string $resource = PlanoMoraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

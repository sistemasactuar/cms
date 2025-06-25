<?php

namespace App\Filament\Resources\PlanoCarteraResource\Pages;

use App\Filament\Resources\PlanoCarteraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanoCarteras extends ListRecords
{
    protected static string $resource = PlanoCarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

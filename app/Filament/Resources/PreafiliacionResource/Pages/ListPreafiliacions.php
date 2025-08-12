<?php

namespace App\Filament\Resources\PreafiliacionResource\Pages;

use App\Filament\Resources\PreafiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreafiliacions extends ListRecords
{
    protected static string $resource = PreafiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

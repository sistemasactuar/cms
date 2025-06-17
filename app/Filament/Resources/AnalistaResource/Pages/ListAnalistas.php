<?php

namespace App\Filament\Resources\AnalistaResource\Pages;

use App\Filament\Resources\AnalistaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnalistas extends ListRecords
{
    protected static string $resource = AnalistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

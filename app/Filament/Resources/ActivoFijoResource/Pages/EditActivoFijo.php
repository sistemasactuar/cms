<?php

namespace App\Filament\Resources\ActivoFijoResource\Pages;

use App\Filament\Resources\ActivoFijoResource;
use Filament\Resources\Pages\EditRecord;

class EditActivoFijo extends EditRecord
{
    protected static string $resource = ActivoFijoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return ActivoFijoResource::getUrl('index');
    }
}

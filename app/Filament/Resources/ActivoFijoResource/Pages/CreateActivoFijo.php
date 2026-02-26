<?php

namespace App\Filament\Resources\ActivoFijoResource\Pages;

use App\Filament\Resources\ActivoFijoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivoFijo extends CreateRecord
{
    protected static string $resource = ActivoFijoResource::class;

    protected function getRedirectUrl(): string
    {
        return ActivoFijoResource::getUrl('index');
    }
}

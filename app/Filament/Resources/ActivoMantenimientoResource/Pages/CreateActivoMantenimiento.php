<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Pages;

use App\Filament\Resources\ActivoMantenimientoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivoMantenimiento extends CreateRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getRedirectUrl(): string
    {
        return ActivoMantenimientoResource::getUrl('index');
    }
}

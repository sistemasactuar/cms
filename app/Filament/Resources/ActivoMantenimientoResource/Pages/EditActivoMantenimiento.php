<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Pages;

use App\Filament\Resources\ActivoMantenimientoResource;
use Filament\Resources\Pages\EditRecord;

class EditActivoMantenimiento extends EditRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return ActivoMantenimientoResource::getUrl('index');
    }
}

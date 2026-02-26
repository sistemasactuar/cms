<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Pages;

use App\Filament\Resources\ActivoMantenimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivoMantenimiento extends EditRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ActivoMantenimientoResource::getUrl('index');
    }
}

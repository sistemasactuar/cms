<?php

namespace App\Filament\Resources\AnalistaResource\Pages;

use App\Filament\Resources\AnalistaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAnalista extends CreateRecord
{
    protected static string $resource = AnalistaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usuario Creado';
    }
}

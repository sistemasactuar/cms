<?php

namespace App\Filament\Resources\ParametroResource\Pages;

use App\Filament\Resources\ParametroResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateParametro extends CreateRecord
{
    protected static string $resource = ParametroResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Parametro Creado';
    }
}

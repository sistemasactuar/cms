<?php

namespace App\Filament\Resources\PlanoMoraResource\Pages;

use App\Filament\Resources\PlanoMoraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanoMora extends CreateRecord
{
    protected static string $resource = PlanoMoraResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Plano Mora Creado';
    }
}

<?php

namespace App\Filament\Resources\PlanoCarteraResource\Pages;

use App\Filament\Resources\PlanoCarteraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanoCartera extends CreateRecord
{
    protected static string $resource = PlanoCarteraResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Plano Cartera Creado';
    }
}

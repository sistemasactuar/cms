<?php

namespace App\Filament\Resources\ObligacionResource\Pages;

use App\Filament\Resources\ObligacionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateObligacion extends CreateRecord
{
    protected static string $resource = ObligacionResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Obligacion Creada';
    }
}

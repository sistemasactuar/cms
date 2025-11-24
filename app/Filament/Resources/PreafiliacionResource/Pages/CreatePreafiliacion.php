<?php

namespace App\Filament\Resources\PreafiliacionResource\Pages;

use App\Filament\Resources\PreafiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePreafiliacion extends CreateRecord
{
    protected static string $resource = PreafiliacionResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Preafiliacion Creada';
    }
}

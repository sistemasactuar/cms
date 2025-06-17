<?php

namespace App\Filament\Resources\AnalistaResource\Pages;

use App\Filament\Resources\AnalistaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnalista extends EditRecord
{
    protected static string $resource = AnalistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuario editado';
    }
}

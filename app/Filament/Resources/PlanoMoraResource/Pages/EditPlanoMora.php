<?php

namespace App\Filament\Resources\PlanoMoraResource\Pages;

use App\Filament\Resources\PlanoMoraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanoMora extends EditRecord
{
    protected static string $resource = PlanoMoraResource::class;

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
        return 'Plano Mora editado';
    }
}

<?php

namespace App\Filament\Resources\Plano4111Resource\Pages;

use App\Filament\Resources\Plano4111Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlano4111 extends EditRecord
{
    protected static string $resource = Plano4111Resource::class;

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
        return 'Plano 4111 editado';
    }
}

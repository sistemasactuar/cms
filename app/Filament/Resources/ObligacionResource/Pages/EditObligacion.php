<?php

namespace App\Filament\Resources\ObligacionResource\Pages;

use App\Filament\Resources\ObligacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObligacion extends EditRecord
{
    protected static string $resource = ObligacionResource::class;

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
        return 'Obligacion editada';
    }
}

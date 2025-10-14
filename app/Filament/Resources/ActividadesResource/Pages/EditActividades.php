<?php

namespace App\Filament\Resources\ActividadesResource\Pages;

use App\Filament\Resources\ActividadesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActividades extends EditRecord
{
    protected static string $resource = ActividadesResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $state = $this->form->getState();

        $data['latitud']  = isset($state['latitud'])  && $state['latitud']  !== '' ? (float) $state['latitud']  : null;
        $data['longitud'] = isset($state['longitud']) && $state['longitud'] !== '' ? (float) $state['longitud'] : null;

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Actividad Modificada';
    }
}

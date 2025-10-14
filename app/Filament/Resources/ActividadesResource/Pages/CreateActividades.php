<?php

namespace App\Filament\Resources\ActividadesResource\Pages;

use App\Filament\Resources\ActividadesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateActividades extends CreateRecord
{
    protected static string $resource = ActividadesResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $state = $this->form->getState();

        $data['latitud']  = $state['latitud']  ?? request()->input('data.latitud')  ?? null;
        $data['longitud'] = $state['longitud'] ?? request()->input('data.longitud') ?? null;

        // 3) normaliza a float o null
        $data['latitud']  = is_numeric($data['latitud'])  ? (float) $data['latitud']  : null;
        $data['longitud'] = is_numeric($data['longitud']) ? (float) $data['longitud'] : null;

        // 4) asegura user_id
        $data['user_id'] = Auth::id();

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Actividad Programada';
    }
}

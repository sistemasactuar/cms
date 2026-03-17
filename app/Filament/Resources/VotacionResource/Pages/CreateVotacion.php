<?php

namespace App\Filament\Resources\VotacionResource\Pages;

use App\Filament\Resources\VotacionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVotacion extends CreateRecord
{
    protected static string $resource = VotacionResource::class;

    public function getSubheading(): ?string
    {
        return 'Guarda primero la configuracion. Al terminar te llevaremos a la edicion para cargar candidatos o planillas.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['tipo_votacion'] ?? 'nominal') !== 'nominal') {
            $data['max_selecciones'] = 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', [
            'record' => $this->getRecord(),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Votacion creada. Ahora completa los candidatos o planillas en la parte inferior.';
    }
}

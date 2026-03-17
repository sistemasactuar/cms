<?php

namespace App\Filament\Resources\VotacionResource\Pages;

use App\Filament\Resources\VotacionResource;
use Filament\Resources\Pages\EditRecord;

class EditVotacion extends EditRecord
{
    protected static string $resource = VotacionResource::class;

    public function getSubheading(): ?string
    {
        return $this->getRecord()->tipo_votacion === 'planilla'
            ? 'Abajo podras crear planillas y luego asignar los candidatos a cada una.'
            : 'Abajo podras crear directamente los candidatos que recibiran los votos.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['tipo_votacion'] ?? 'nominal') !== 'nominal') {
            $data['max_selecciones'] = 1;
        }

        return $data;
    }
}

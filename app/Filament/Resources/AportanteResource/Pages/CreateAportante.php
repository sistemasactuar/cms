<?php

namespace App\Filament\Resources\AportanteResource\Pages;

use App\Filament\Resources\AportanteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAportante extends CreateRecord
{
    protected static string $resource = AportanteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = filled($data['password'] ?? null)
            ? $data['password']
            : $data['documento'];

        return $data;
    }
}

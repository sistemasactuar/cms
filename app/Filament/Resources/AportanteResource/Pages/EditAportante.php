<?php

namespace App\Filament\Resources\AportanteResource\Pages;

use App\Filament\Resources\AportanteResource;
use Filament\Resources\Pages\EditRecord;

class EditAportante extends EditRecord
{
    protected static string $resource = AportanteResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}

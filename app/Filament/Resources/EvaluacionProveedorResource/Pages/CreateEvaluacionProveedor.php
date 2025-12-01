<?php

namespace App\Filament\Resources\EvaluacionProveedorResource\Pages;

use App\Filament\Resources\EvaluacionProveedorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluacionProveedor extends CreateRecord
{
    protected static string $resource = EvaluacionProveedorResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Evaluacion Proveedor Creada';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}

<?php

namespace App\Filament\Resources\PlanoCarteraResource\Pages;

use App\Filament\Resources\PlanoCarteraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanoCartera extends EditRecord
{
    protected static string $resource = PlanoCarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

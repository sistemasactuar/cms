<?php

namespace App\Filament\Resources\PreafiliacionResource\Pages;

use App\Filament\Resources\PreafiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPreafiliacion extends EditRecord
{
    protected static string $resource = PreafiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\EvaluacionProveedorResource\Pages;

use App\Filament\Resources\EvaluacionProveedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluacionProveedors extends ListRecords
{
    protected static string $resource = EvaluacionProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

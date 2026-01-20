<?php

namespace App\Filament\Resources\PlanoSaldoValorResource\Pages;

use App\Filament\Resources\PlanoSaldoValorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePlanoSaldoValors extends ManageRecords
{
    protected static string $resource = PlanoSaldoValorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

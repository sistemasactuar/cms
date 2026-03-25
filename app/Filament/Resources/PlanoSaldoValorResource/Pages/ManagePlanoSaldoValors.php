<?php

namespace App\Filament\Resources\PlanoSaldoValorResource\Pages;

use App\Filament\Resources\PlanoSaldoValorResource;
use App\Filament\Resources\PlanoSaldoValorResource\Widgets\PlanoSaldoValorStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePlanoSaldoValors extends ManageRecords
{
    protected static string $resource = PlanoSaldoValorResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PlanoSaldoValorStatsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

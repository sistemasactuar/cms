<?php

namespace App\Filament\Resources\PlanoSaldoValorResource\Widgets;

use App\Models\PlanoSaldoValor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlanoSaldoValorStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRegistros = PlanoSaldoValor::query()->count();
        $conSaldoVencido = PlanoSaldoValor::query()->conSaldoVencido();
        $alDia = PlanoSaldoValor::query()->alDia();
        $saldoCero = PlanoSaldoValor::query()->conSaldoCero();

        return [
            Stat::make('Total Registros', number_format($totalRegistros, 0, ',', '.'))
                ->description('Base consolidada actual')
                ->icon('heroicon-o-document-text')
                ->color('primary'),
            Stat::make('Con Saldo Vencido', number_format((clone $conSaldoVencido)->count(), 0, ',', '.'))
                ->description('Valor vencido total: ' . $this->formatCurrency((clone $conSaldoVencido)->sum('valor_vencido')))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
            Stat::make('Al Dia', number_format((clone $alDia)->count(), 0, ',', '.'))
                ->description('Sin valor vencido pendiente')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
            Stat::make('Saldo Capital 0', number_format((clone $saldoCero)->count(), 0, ',', '.'))
                ->description('Registros cerrados o excluidos del plano')
                ->icon('heroicon-o-minus-circle')
                ->color('warning'),
        ];
    }

    private function formatCurrency(float|int|string|null $value): string
    {
        return '$' . number_format((float) $value, 0, ',', '.');
    }
}

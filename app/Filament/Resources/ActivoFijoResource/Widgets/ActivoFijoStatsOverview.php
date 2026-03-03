<?php

namespace App\Filament\Resources\ActivoFijoResource\Widgets;

use App\Models\ActivoFijo;
use App\Models\ActivoMantenimiento;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivoFijoStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalActivos = ActivoFijo::query()->count();
        $activosHabilitados = ActivoFijo::query()->where('activo', true)->count();
        $activosBaja = max(0, $totalActivos - $activosHabilitados);
        $totalMantenimientos = ActivoMantenimiento::query()->count();
        $mantenimientosMes = ActivoMantenimiento::query()
            ->whereDate('fecadi', '>=', now()->startOfMonth()->toDateString())
            ->count();

        return [
            Stat::make('Total Activos', number_format($totalActivos, 0, ',', '.'))
                ->description('Registros en inventario')
                ->icon('heroicon-o-computer-desktop')
                ->color('primary'),
            Stat::make('Activos Habilitados', number_format($activosHabilitados, 0, ',', '.'))
                ->description('Equipos en uso')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Activos de Baja', number_format($activosBaja, 0, ',', '.'))
                ->description('Equipos inactivos')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('Mantenimientos Mes', number_format($mantenimientosMes, 0, ',', '.'))
                ->description('Total historico: ' . number_format($totalMantenimientos, 0, ',', '.'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning'),
        ];
    }
}


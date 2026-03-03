<?php

namespace App\Filament\Resources\ActivoMantenimientoResource\Widgets;

use App\Models\ActivoMantenimiento;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivoMantenimientoStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = ActivoMantenimiento::query()->count();
        $activosIntervenidos = ActivoMantenimiento::query()->distinct('equipo_id')->count('equipo_id');
        $mesActual = ActivoMantenimiento::query()
            ->whereDate('fecadi', '>=', now()->startOfMonth()->toDateString())
            ->count();
        $correctivos = ActivoMantenimiento::query()->where('tipo_M', 1)->count();

        return [
            Stat::make('Total Mantenimientos', number_format($total, 0, ',', '.'))
                ->description('Historico acumulado')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),
            Stat::make('Mantenimientos Mes', number_format($mesActual, 0, ',', '.'))
                ->description('Desde ' . now()->startOfMonth()->format('Y-m-d'))
                ->icon('heroicon-o-calendar-days')
                ->color('success'),
            Stat::make('Correctivos', number_format($correctivos, 0, ',', '.'))
                ->description('Tipo Correctivo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),
            Stat::make('Activos Intervenidos', number_format($activosIntervenidos, 0, ',', '.'))
                ->description('Equipos con mantenimiento')
                ->icon('heroicon-o-computer-desktop')
                ->color('info'),
        ];
    }
}


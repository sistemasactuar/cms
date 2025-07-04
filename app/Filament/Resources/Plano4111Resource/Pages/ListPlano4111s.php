<?php

namespace App\Filament\Resources\Plano4111Resource\Pages;

use App\Filament\Resources\Plano4111Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Route;

class ListPlano4111s extends ListRecords
{
    protected static string $resource = Plano4111Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Importar XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(route('filament.admin.pages.plano4111.importar')) // Usa el nombre manual
                ->openUrlInNewTab(),
        ];

    }
}

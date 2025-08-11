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
        $user = auth()->user();

        // Mostrar el botón "Crear" solo si el usuario tiene el permiso
        if ($user->hasRole(['admin', 'superadmin']) || $user->can('Crear Recurso')) {
            return [
               // Actions\CreateAction::make(),
            ];
        }

        // Si no tiene permisos, no muestra ninguna acción
        return [];
    }
}

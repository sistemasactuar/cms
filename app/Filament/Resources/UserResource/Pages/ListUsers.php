<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // Mostrar el botón "Crear" solo si el usuario tiene el permiso
        if ($user->hasRole(['admin', 'superadmin']) || $user->can('Crear Usuario')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        // Si no tiene permisos, no muestra ninguna acción
        return [];
    }
}

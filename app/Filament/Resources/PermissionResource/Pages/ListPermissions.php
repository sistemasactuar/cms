<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // Mostrar el botón "Crear" solo si el usuario tiene el permiso
        if ($user->hasRole(['admin', 'superadmin']) || $user->can('Crear Recurso')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        // Si no tiene permisos, no muestra ninguna acción
        return [];
    }
}

<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // Mostrar el botón "Crear" solo si el usuario tiene el permiso
        if ($user->hasRole(['admin', 'superadmin']) || $user->can('Crear Roll')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        // Si no tiene permisos, no muestra ninguna acción
        return [];
    }
}

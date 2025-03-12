<?php

namespace App\Filament\Resources\SedeResource\Pages;

use App\Filament\Resources\SedeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSedes extends ListRecords
{
    protected static string $resource = SedeResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        // Si es administrador o superadministrador, puede acceder
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }

        // Si tiene permisos explícitos, también puede acceder
        if ($user->can('Editar Sede')) {
            return true;
        }

        // Redirigir al listado con notificación si no tiene permisos
        Notification::make()
            ->title('Acceso Denegado')
            ->body('No tienes los recursos necesarios para el menu sedes.')
            ->warning()
            ->send();

        return false;
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // Mostrar el botón "Crear" solo si el usuario tiene el permiso
        if ($user->hasRole(['admin', 'superadmin']) || $user->can('Crear Sede')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        // Si no tiene permisos, no muestra ninguna acción
        return [];
    }
}

<?php

namespace App\Filament\Resources\SedeResource\Pages;

use App\Filament\Resources\SedeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSede extends CreateRecord
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
            ->body('No tienes los recursos necesarios para crear sedes.')
            ->warning()
            ->send();

        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Sede Creada';
    }
}

<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        // Si es administrador o superadministrador, puede acceder
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }

        // Si tiene permisos explícitos, también puede acceder
        if ($user->can('Crear Aplicacion')) {
            return true;
        }

        // Redirigir al listado con notificación si no tiene permisos
        Notification::make()
            ->title('Acceso Denegado')
            ->body('No tienes los recursos necesarios para crear aplicaciones.')
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
        return 'Aplicación Creada';
    }
}

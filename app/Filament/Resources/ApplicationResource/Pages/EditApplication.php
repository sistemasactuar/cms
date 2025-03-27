<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        // Si es administrador o superadministrador, puede acceder
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }

        // Si tiene permisos explícitos, también puede acceder
        if ($user->can('Editar Aplicacion')) {
            return true;
        }

        // Redirigir al listado con notificación si no tiene permisos
        Notification::make()
            ->title('Acceso Denegado')
            ->body('No tienes los recursos necesarios para editar Aplicaciones.')
            ->warning()
            ->send();

        return false;
    }

}

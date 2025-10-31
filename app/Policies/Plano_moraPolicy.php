<?php

namespace App\Policies;

use App\Models\Plano_mora;
use App\Models\User;

class Plano_moraPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plano_mora $Plano_mora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plano_mora $Plano_mora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plano_mora $Plano_mora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, Plano_mora $Plano_mora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano_mora Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plano_mora $Plano_mora): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plano_mora $Plano_mora): bool
    {
        return false;
    }
}

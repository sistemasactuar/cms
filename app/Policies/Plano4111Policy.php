<?php

namespace App\Policies;

use App\Models\Plano4111;
use App\Models\User;

class Plano4111Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plano4111 $Plano4111): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111 Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plano4111 $Plano4111): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111 Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plano4111 $Plano4111): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111 Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, Plano4111 $Plano4111): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Plano4111 Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plano4111 $Plano4111): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plano4111 $Plano4111): bool
    {
        return false;
    }
}

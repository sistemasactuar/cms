<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Application $Application): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $Application): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Application $Application): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, Application $Application): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Application Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Application $Application): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Application $Application): bool
    {
        return false;
    }
}

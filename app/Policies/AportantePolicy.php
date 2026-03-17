<?php

namespace App\Policies;

use App\Models\Aportante;
use App\Models\User;

class AportantePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante') || $user->can('Aportantes')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Aportante $aportante): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante') || $user->can('Aportantes')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante Crear') || $user->can('Aportantes Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Aportante $aportante): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante Editar') || $user->can('Aportantes Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Aportante $aportante): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante Inactivar') || $user->can('Aportantes Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, Aportante $aportante): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Aportante Inactivar') || $user->can('Aportantes Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Aportante $aportante): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Aportante $aportante): bool
    {
        return false;
    }
}

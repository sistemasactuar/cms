<?php

namespace App\Policies;

use App\Models\Votacion;
use App\Models\User;

class VotacionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Votacion $votacion): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Crear') || $user->can('Votaciones Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Votacion $votacion): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Editar') || $user->can('Votaciones Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Votacion $votacion): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Inactivar') || $user->can('Votaciones Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, Votacion $votacion): bool
    {
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Inactivar') || $user->can('Votaciones Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Votacion $votacion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Votacion $votacion): bool
    {
        return false;
    }
}

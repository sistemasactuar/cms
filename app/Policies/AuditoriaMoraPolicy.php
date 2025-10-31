<?php

namespace App\Policies;

use App\Models\AuditoriaMora;
use App\Models\User;

class AuditoriaMoraPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuditoriaMora $AuditoriaMora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuditoriaMora $AuditoriaMora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuditoriaMora $AuditoriaMora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, AuditoriaMora $AuditoriaMora): bool
    {// Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('AuditoriaMora Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuditoriaMora $AuditoriaMora): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuditoriaMora $AuditoriaMora): bool
    {
        return false;
    }
}

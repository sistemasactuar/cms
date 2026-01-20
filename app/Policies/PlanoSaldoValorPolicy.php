<?php

namespace App\Policies;

use App\Models\PlanoSaldoValor;
use App\Models\User;

class PlanoSaldoValorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor Crear')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor Editar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor Inactivar')) {
            return true;
        }

        return false;
    }

    public function inactivar(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        // Si es administrador, siempre tiene permiso
        if ($user->hasRole(['admin', 'Superadmin']) || $user->can('PlanoSaldoValor Inactivar')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlanoSaldoValor $planoSaldoValor): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Proveedores;
use App\Models\User;

class ProveedoresPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('ver proveedor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Proveedores $proveedores): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('ver proveedor');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('crear proveedor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Proveedores $proveedores): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('editar proveedor');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Proveedores $proveedores): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('eliminar proveedor');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Proveedores $proveedores): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('eliminar proveedor');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Proveedores $proveedores): bool
    {
        if ($user->hasRole('superadministrador')) {
            return true;
        }
        return $user->can('eliminar proveedor');
    }
}

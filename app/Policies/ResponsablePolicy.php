<?php

namespace App\Policies;

use App\Models\Responsable;
use App\Models\User;

class ResponsablePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('ver responsable');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Responsable $responsable): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('ver responsable');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('crear responsable');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Responsable $responsable): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('editar responsable');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Responsable $responsable): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar responsable');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Responsable $responsable): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar responsable');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Responsable $responsable): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar responsable');
    }
}

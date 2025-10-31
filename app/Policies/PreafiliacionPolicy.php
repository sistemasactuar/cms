<?php

namespace App\Policies;

use App\Models\Preafiliacion;
use App\Models\User;

class PreafiliacionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Preafiliacion $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Preafiliacion $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Preafiliacion $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Preafiliacion $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Preafiliacion $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }
}

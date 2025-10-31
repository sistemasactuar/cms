<?php

namespace App\Policies;

use App\Models\Tercero;
use App\Models\User;

class TerceroPolicy
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
    public function view(User $user, Tercero $model): bool
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
    public function update(User $user, Tercero $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tercero $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tercero $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tercero $model): bool
    {
        return $user->hasRole(['admin', 'Superadmin']);
    }
}

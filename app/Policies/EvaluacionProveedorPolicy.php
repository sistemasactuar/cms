<?php

namespace App\Policies;

use App\Models\EvaluacionProveedor;
use App\Models\User;

class EvaluacionProveedorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        if ($user->hasRole(['superadministrador', 'admin'])) {
            return true;
        }

        return $user->id === $evaluacionProveedor->user_id || $user->id === $evaluacionProveedor->responsable_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        if ($user->hasRole(['superadministrador', 'admin'])) {
            return true;
        }

        // Users can only update their own evaluations if they are not blocked (signed)
        return ($user->id === $evaluacionProveedor->user_id || $user->id === $evaluacionProveedor->responsable_id) 
            && ! $evaluacionProveedor->bloqueado;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        return $user->hasRole(['superadministrador', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        return $user->hasRole(['superadministrador', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        return $user->hasRole(['superadministrador', 'admin']);
    }
}

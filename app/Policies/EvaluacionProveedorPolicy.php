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
        if ($user->hasRole('Superadmin') || $user->hasRole('Asistente Administrativa')) {
            return true;
        }
        // Allow if user has permission OR is a responsable (so they can see their own)
        return $user->can('ver evaluacion_proveedor') || !empty($user->responsable_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        // Allow if user has permission OR is a responsable (they need to create evaluations)
        return $user->can('crear evaluacion_proveedor') || !empty($user->responsable_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->can('editar evaluacion_proveedor')) {
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
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar evaluacion_proveedor');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar evaluacion_proveedor');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EvaluacionProveedor $evaluacionProveedor): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }
        return $user->can('eliminar evaluacion_proveedor');
    }
}

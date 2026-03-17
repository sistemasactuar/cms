<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VotacionPlanilla;

class VotacionPlanillaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones');
    }

    public function view(User $user, VotacionPlanilla $votacionPlanilla): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Crear') || $user->can('Votaciones Crear');
    }

    public function update(User $user, VotacionPlanilla $votacionPlanilla): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Editar') || $user->can('Votaciones Editar');
    }

    public function delete(User $user, VotacionPlanilla $votacionPlanilla): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Inactivar') || $user->can('Votaciones Inactivar');
    }

    public function restore(User $user, VotacionPlanilla $votacionPlanilla): bool
    {
        return false;
    }

    public function forceDelete(User $user, VotacionPlanilla $votacionPlanilla): bool
    {
        return false;
    }
}

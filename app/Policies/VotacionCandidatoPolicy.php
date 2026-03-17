<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VotacionCandidato;

class VotacionCandidatoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones');
    }

    public function view(User $user, VotacionCandidato $votacionCandidato): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion') || $user->can('Votaciones');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Crear') || $user->can('Votaciones Crear');
    }

    public function update(User $user, VotacionCandidato $votacionCandidato): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Editar') || $user->can('Votaciones Editar');
    }

    public function delete(User $user, VotacionCandidato $votacionCandidato): bool
    {
        return $user->hasRole(['admin', 'Superadmin']) || $user->can('Votacion Inactivar') || $user->can('Votaciones Inactivar');
    }

    public function restore(User $user, VotacionCandidato $votacionCandidato): bool
    {
        return false;
    }

    public function forceDelete(User $user, VotacionCandidato $votacionCandidato): bool
    {
        return false;
    }
}

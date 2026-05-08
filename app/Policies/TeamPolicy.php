<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    // Solo el dueño de la organización puede crear, editar, eliminar
    // y gestionar miembros del equipo
    public function manage(User $user, Team $team): bool
    {
        return $user->id === $team->organization->owner_id;
    }

    // El dueño O cualquier miembro del equipo puede verlo
    public function view(User $user, Team $team): bool
    {
        $isOwner = $user->id === $team->organization->owner_id;

        $isMember = $team->members()
            ->where('user_id', $user->id)
            ->exists();

        return $isOwner || $isMember;
    }
}
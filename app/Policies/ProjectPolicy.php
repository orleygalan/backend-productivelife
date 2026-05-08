<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // Solo el dueño de la organización o un admin del equipo
    // puede crear, editar o eliminar proyectos
    public function manage(User $user, Project $project): bool
    {
        $team = $project->team;

        $isOwner = $user->id === $team->organization->owner_id;

        $isAdmin = $team->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();

        return $isOwner || $isAdmin;
    }

    // El dueño O cualquier miembro del equipo puede ver el proyecto y sus tareas
    public function view(User $user, Project $project): bool
    {
        $team = $project->team;

        $isOwner = $user->id === $team->organization->owner_id;

        $isMember = $team->members()
            ->where('user_id', $user->id)
            ->exists();

        return $isOwner || $isMember;
    }
}
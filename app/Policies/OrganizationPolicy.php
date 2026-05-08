<?php
namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    // El dueño O cualquier miembro del equipo puede verlas
    public function view(User $user, Organization $organization): bool
    {
        $isOwner = $user->id === $organization->owner_id;

        $isMember = $organization->teams()
            ->whereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->exists();

        return $isOwner || $isMember;
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->id === $organization->owner_id;
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->id === $organization->owner_id;
    }
}

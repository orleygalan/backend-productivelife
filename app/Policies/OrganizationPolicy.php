<?php
namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{

    public function view(User $user, Organization $organization): bool
    {
        return $user->id === $organization->owner_id;
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

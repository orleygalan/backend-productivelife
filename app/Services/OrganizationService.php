<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationService
{
    public function getAll()
    {
        $userId = Auth::id();

        return Organization::where('owner_id', $userId)
            ->orWhereHas('teams.members', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();
    }

    // crear una organizacion 
    public function store(array $data)
    {
        return Organization::create([
            'name' => $data['name'],
            'owner_id' => Auth::id(),
        ]);
    }

    // Mostrar una organizacion 
    public function show(Organization $organization)
    {
        return $organization->load('teams');
    }

    // actualizar una Organization 
    public function update(Organization $organization, array $data)
    {
        $organization->update($data);
        return $organization->fresh();
    }

    // Eliminar una organizacion 
    public function destroy(Organization $organization): void
    {
        $organization->delete();
    }

}
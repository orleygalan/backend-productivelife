<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class TeamService
{

    // Mostrar los equipos de una organizacion 
    public function getAll(Organization $organization)
    {
        $userId = Auth::id();

        if ($organization->owner_id === $userId) {
            return $organization->teams()->get();
        }

        return $organization->teams()
            ->whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->get();
    }
    // Crear un equipo dentro de una organizacion 
    public function store(array $data)
    {

        $team = Team::create([
            'name' => $data['name'],
            'organization_id' => $data['organization_id'],
        ]);
        // El creador del equipo se agrega automáticamente como admin
        $team->members()->attach(Auth::id(), ['role' => 'admin']);

        return $team->load('members');
    }


    // Mostrar un equipo con sus miembros y proyectos
    public function show(Team $team)
    {
        return $team->load(['members', 'projects']);
    }

    // Actualizar un equipo 
    public function update(Team $team, array $data)
    {
        $team->update($data);
        return $team->refresh();
    }

    // Eliminar un equipo
    public function destroy(Team $team): void
    {
        $team->delete();
    }

    // Agregar miembro al equipo
    public function addMember(Team $team, string $email, string $role = 'editor'): void
    {
        // Buscamos ID por email para la tabla intermedia/pivot
        $user = User::where('email', $email)->firstOrFail();

        $team->members()->attach($user->id, ['role' => $role]);
    }

    // Eliminar miembro del equipo
    public function removeMember(Team $team, string $userId): void
    {
        $team->members()->detach($userId);
    }

}
<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Team;

class ProjectService
{
    // Listar los proyectos de un equipo
    public function getAll(Team $team)
    {
        return $team->projects()->get();

    }

    // Crear un proyecto
    public function store(array $data)
    {

        return Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'active',
            'team_id' => $data['team_id']
        ]);

    }

    // Mostrar un proyectos con todas sus tareas 
    public function show(Project $project)
    {
        return [
            'project' => $project,
            'tasks' => $project->tasksByStatus(),
        ];
    }

    // Actualizar equipo
    public function update(Project $project, array $data)
    {
        $project->update($data);
        return $project->fresh();
    }

    // Eliminar equipo
    public function destroy(Project $project): void
    {
        $project->delete();
    }

}
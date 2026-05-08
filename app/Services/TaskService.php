<?php
namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{

    public function getAll(Project $project)
    {
        return $project->tasks()->with('assignedTo')->paginate(20);
    }

    // crear tarea 
    public function store(array $data)
    {
        $project = Project::findOrFail($data['project_id']);

        // Si se asigna a alguien, verificar que es miembro del equipo
        if (isset($data['assigned_to'])) {
            $this->checkAssignedIsMember($project, $data['assigned_to']);
        }

        return Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'project_id' => $data['project_id'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'todo',
        ]);
    }

    // Mostrar tarea 
    public function show(Task $task)
    {
        return $task->load('assignedTo');

    }
    // Actualizar tarea 
    public function update(Task $task, array $data)
    {
        // Si se reasigna, verificar que el nuevo usuario es miembro
        if (isset($data['assigned_to'])) {
            $this->checkAssignedIsMember($task->project, $data['assigned_to']);
        }

        $task->update($data);
        return $task->fresh()->load('assignedTo');
    }

    // Eliminar tarea 
    public function destroy(Task $task): void
    {
        $task->delete();
    }

    // Cambiar estado de la tarea (para el kanba drag y drop )
    public function changeStatus(Task $task, string $status)
    {
        $task->update(['status' => $status]);
        return $task->fresh();
    }

    // Verificar que el usuario asignado también es miembro del equipo
    private function checkAssignedIsMember(Project $project, string $userId): void
    {
        $isMember = $project->team
            ->members()
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            abort(403, 'El usuario asignado no es miembro de este equipo.');
        }
    }

}
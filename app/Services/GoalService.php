<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalTask;
use Illuminate\Support\Facades\Auth;

class GoalService
{
    // Listar metas del usuario
    public function getAll()
    {
        return Goal::where('user_id', Auth::id())
            ->with('tasks')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Crear meta
    public function store(array $data)
    {
        $term = Goal::calculateTerm($data['start_date'], $data['end_date']);
        $range = Goal::xpRange($term);

        // Validar XP de cada tarea segun el term
        foreach ($data['tasks'] as $task) {
            if ($task['xp_per_day'] < $range['min'] || $task['xp_per_day'] > $range['max']) {
                abort(422, "La tarea '{$task['title']}' debe tener entre {$range['min']} y {$range['max']} XP para una meta de " . Goal::termLabel($term) . ".");
            }
        }

        $goal = Goal::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'term' => $term,
            'status' => 'active',
        ]);

        // Crear las tareas diarias de la meta
        foreach ($data['tasks'] as $task) {
            $goal->tasks()->create([
                'title' => $task['title'],
                'xp_per_day' => $task['xp_per_day'],
            ]);
        }

        return $goal->load('tasks');
    }


    // Mostrar una meta
    public function show(Goal $goal)
    {
        return $goal->load('tasks');
    }

    // Actualizar meta
    public function update(Goal $goal, array $data)
    {

        // Si cambia la fecha, recalcular el term
        if (isset($data['end_date'])) {
            $data['term'] = Goal::calculateTerm(
                $goal->start_date->toDateString(),
                $data['end_date']
            );
        }

        $goal->update($data);
        return $goal->fresh('tasks');
    }

    // Eliminar meta
    public function destroy(Goal $goal): void
    {
        $goal->delete();
    }

    // Agregar tarea a una meta existente
    public function addTask(Goal $goal, array $data)
    {

        // Validar rango de XP segun el term de la meta
        $range = Goal::xpRange($goal->term);

        if ($data['xp_per_day'] < $range['min'] || $data['xp_per_day'] > $range['max']) {
            abort(422, "El XP debe estar entre {$range['min']} y {$range['max']} para una meta de {$goal->termLabel()}.");
        }

        return $goal->tasks()->create([
            'title' => $data['title'],
            'xp_per_day' => $data['xp_per_day'],
        ]);
    }

    // Actualizar tarea
    public function updateTask(Goal $goal, GoalTask $task, array $data)
    {
        // Verificar que la tarea pertenece a esta meta}
        if ($task->goal_id !== $goal->id) {
            abort(403, 'Esta tarea no pertenece a esta meta.');
        }

        // Solo editable el mismo dia de creación
        if (!$task->isEditable()) {
            abort(403, 'Esta tarea ya no puede modificarse después del día de su creación.');
        }

        // Validar rango de XP
        if (isset($data['xp_per_day'])) {
            $range = Goal::xpRange($goal->term);
            if ($data['xp_per_day'] < $range['min'] || $data['xp_per_day'] > $range['max']) {
                abort(422, "El XP debe estar entre {$range['min']} y {$range['max']} para una meta de {$goal->termLabel()}.");
            }
        }

        $task->update($data);
        return $task->fresh();
    }

    // Eliminar tarea
    public function deleteTask(Goal $goal, GoalTask $task): void
    {
        // Verificar que la tarea pertenece a esta meta
        if ($task->goal_id !== $goal->id) {
            abort(403, 'Esta tarea no pertenece a esta meta.');
        }

        // Solo eliminable el mismo dia de creación
        if (!$task->isEditable()) {
            abort(403, 'Esta tarea ya no puede eliminarse después del día de su creación.');
        }

        // Debe quedar al menos 2 tareas
        if ($goal->tasks()->count() <= 2) {
            abort(422, 'La meta debe tener al menos 2 tareas diarias.');
        }

        $task->delete();
    }
}
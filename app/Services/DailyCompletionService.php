<?php

namespace App\Services;

use App\Models\DailyCompletion;
use App\Models\Goal;
use App\Models\GoalTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyCompletionService
{
    public function __construct(private PointService $pointService)
    {
    }

    // Completar una tarea diaria
    public function complete(Goal $goal, GoalTask $task)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        // Verificar que la meta esta activa
        if ($goal->status !== 'active') {
            abort(422, 'Esta meta ya no está activa.');
        }

        // Verificar que la meta no ha vencido
        if ($goal->isExpired()) {
            abort(422, 'Esta meta ya venció.');
        }

        // Verificar que la tarea pertenece a la meta
        if ($task->goal_id !== $goal->id) {
            abort(422, 'Esta tarea no pertenece a esta meta.');
        }

        // Verificar que no fue completada hoy
        if ($task->isCompletedToday($userId)) {
            abort(422, 'Esta tarea ya fue completada hoy.');
        }

        // Verificar ventana de tiempo: 01:00 AM a 11:59 PM
        $now = Carbon::now();
        $from = Carbon::today()->setHour(1)->setMinute(0);
        $to = Carbon::today()->setHour(23)->setMinute(59);

        if ($now->lt($from) || $now->gt($to)) {
            abort(422, 'Las tareas solo se pueden completar entre la 1:00 AM y las 11:59 PM.');
        }

        return DB::transaction(function () use ($goal, $task, $userId, $today) {
            // Registrar completion
            $completion = DailyCompletion::create([
                'goal_task_id' => $task->id,
                'goal_id' => $goal->id,
                'user_id' => $userId,
                'completed_date' => $today,
                'xp_earned' => $task->xp_per_day,
            ]);

            // Sumar puntos al usuario
            $this->pointService->addPoints(
                userId: $userId,
                amount: $task->xp_per_day,
                type: 'daily_task',
                description: "Tarea '{$task->title}' completada — Meta: '{$goal->title}'",
                goalId: $goal->id,
                goalTaskId: $task->id,
            );

            // Actualizar racha si todas las tareas del dia fueron completadas
            $this->updateStreak($goal, $userId, $today);

            return $completion;
        });
    }

    //Descompletar una tarea diaria
    public function uncomplete(Goal $goal, GoalTask $task): void
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $completion = DailyCompletion::where('goal_task_id', $task->id)
            ->where('user_id', $userId)
            ->whereDate('completed_date', $today)
            ->first();

        if (!$completion) {
            abort(422, 'Esta tarea no fue completada hoy.');
        }

        DB::transaction(function () use ($completion, $goal, $task, $userId) {
            // Restar puntos
            $this->pointService->subtractPoints(
                userId: $userId,
                amount: $task->xp_per_day,
                type: 'daily_task',
                description: "Tarea '{$task->title}' desmarcada — Meta: '{$goal->title}'",
                goalId: $goal->id,
                goalTaskId: $task->id,
            );

            $completion->delete();

            // Recalcular racha
            $this->recalculateStreak($goal, $userId);
        });
    }

    // Obtener tareas del dia para una meta
    public function getTodayTasks(Goal $goal): array
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        return $goal->tasks->map(function (GoalTask $task) use ($userId, $today) {
            $completion = DailyCompletion::where('goal_task_id', $task->id)
                ->where('user_id', $userId)
                ->where('completed_date', $today)
                ->first();

            $task->completed = !is_null($completion);
            $task->completed_at = $completion?->created_at;

            return $task;
        });
    }

    // Actualizar racha cuando se completan todas las tareas del dia
    private function updateStreak(Goal $goal, string $userId, string $today): void
    {
        $totalTasks = $goal->tasks()->count();
        $completedToday = DailyCompletion::where('goal_id', $goal->id)
            ->where('user_id', $userId)
            ->where('completed_date', $today)
            ->count();

        // Si completo todas las tareas del dia, incrementar racha
        if ($completedToday === $totalTasks) {
            $goal->increment('current_streak');

            // Actualizar max_streak si la racha actual es mayor
            if ($goal->current_streak > $goal->max_streak) {
                $goal->update(['max_streak' => $goal->current_streak]);
            }
        }
    }

    // Recalcular racha al desmarcar
    private function recalculateStreak(Goal $goal, string $userId): void
    {
        $today = Carbon::today()->toDateString();
        $totalTasks = $goal->tasks()->count();
        $completedToday = DailyCompletion::where('goal_id', $goal->id)
            ->where('user_id', $userId)
            ->where('completed_date', $today)
            ->count();

        // Si ya no tiene todas completadas, decrementar racha
        if ($completedToday < $totalTasks && $goal->current_streak > 0) {
            $goal->decrement('current_streak');
        }
    }
}
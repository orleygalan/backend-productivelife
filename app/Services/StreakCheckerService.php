<?php

namespace App\Services;

use App\Models\DailyCompletion;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StreakCheckerService
{

    public function __construct(private PointService $pointService)
    {
    }

    // Se ejecuta cada dia a las 11:59 PM via Scheduler 
    public function checkAllGoals(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        // Obtener todas las metas activas
        $goals = Goal::where('status', 'active')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $yesterday)
            ->with('tasks')
            ->get();

        foreach ($goals as $goal) {
            $this->checkGoal($goal, $yesterday, $today);
        }
    }

    private function checkGoal(Goal $goal, string $yesterday, string $today): void
    {
        $totalTasks = $goal->tasks->count();
        if ($totalTasks === 0)
            return;

        // Verificar si completo todas las tareas ayer
        $completedYesterday = DailyCompletion::where('goal_id', $goal->id)
            ->where('user_id', $goal->user_id)
            ->where('completed_date', $yesterday)
            ->count();

        DB::transaction(function () use ($goal, $totalTasks, $completedYesterday, $today) {
            if ($completedYesterday < $totalTasks) {
                // Fallo el i - romper racha y contar dia fallado
                $goal->update([
                    'current_streak' => 0,
                    'missed_days' => $goal->missed_days + 1,
                ]);
            }

            // Si hoy es el ultimo día de la meta, otorgar bonus
            if ($goal->end_date->toDateString() === $today && !$goal->bonus_granted) {
                $this->pointService->grantStreakBonus($goal);
            }
        });
    }
}
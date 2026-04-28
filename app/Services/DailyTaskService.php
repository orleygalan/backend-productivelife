<?php
namespace App\Services;

use App\Models\DailyPointsLog;
use App\Models\DailyTask;
use App\Models\UserPoints;
use App\Models\WeeklyPointsSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyTaskService
{

    // Mostrar las tareas del dia 
    public function getAll(?string $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return DailyTask::where('user_id', Auth::id())
            ->whereDate('task_date', $date)
            ->get();
    }

    // Crear tarea diaria 
    public function store(array $data)
    {
        return DailyTask::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'xp_reward' => $data['xp_reward'],
            'task_date' => $data['task_date'],
            'completed' => false
        ]);
    }

    // Actualizar tarea 
    public function update(DailyTask $dailyTask, array $data)
    {
        $this->checkOwner($dailyTask);

        if ($dailyTask->completed) {
            abort(422, 'No puedes editar una tarea ya completada');
        }

        $dailyTask->update($data);
        return $dailyTask->fresh();
    }

    // Eliminar tarea diaria 
    public function destroy(DailyTask $dailyTask): void
    {
        $this->checkOwner($dailyTask);

        // Si estaba completada, restar los puntos 
        if ($dailyTask->completed) {
            $this->subtractPoints($dailyTask);
        }

        $dailyTask->delete();
    }

    // Marcar tarea como completada y sumar puntos 
    public function complete(DailyTask $dailyTask)
    {
        $this->checkOwner($dailyTask);

        if ($dailyTask->completed) {
            abort(422, 'Esta tarea ya fue completada .');
        }

        // Marcar como completada 
        $dailyTask->update([
            'completed' => true,
            'completed_at' => Carbon::now(),
        ]);

        // Registrar puntos del dia 
        DailyPointsLog::create([
            'user_id' => Auth::id(),
            'daily_task_id' => $dailyTask->id,
            'points_earned' => $dailyTask->xp_reward,
            'log_date' => Carbon::today(),
        ]);

        // Sumar puntos al resumen semanal
        $this->addToWeeklySummary($dailyTask->xp_reward);

        // Actualizar puntos totales y nivel
        $this->updateUserPoints($dailyTask->xp_reward);

        return $dailyTask->fresh();
    }

    public function uncomplete(DailyTask $dailyTask)
    {
        $this->checkOwner($dailyTask);

        if (!$dailyTask->completed) {
            abort(422, 'Esta tarea no está completada.');
        }

        // Restar puntos
        $this->subtractPoints($dailyTask);

        // Desmarcar
        $dailyTask->update([
            'completed' => false,
            'completed_at' => null,
        ]);

        return $dailyTask->fresh();
    }

    // Sumar puntos al resumen semanal actual
    private function addToWeeklySummary(int $points)
    {
        $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = Carbon::today()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $summary = WeeklyPointsSummary::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'week_start' => $weekStart,
            ],
            [
                'week_end_date' => $weekEnd,
                'total_points' => 0,
            ],
        );

        $summary->increment('total_points', $points);

    }

    // Restar puntos del resumen semanal y del total 
    private function subtractPoints(DailyTask $dailyTask): void
    {
        // Eliminar registro del log 
        DailyPointsLog::where('daily_task_id', $dailyTask->id)->delete();

        // Restal del resumen semanal
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $summary = WeeklyPointsSummary::where('user_id', Auth::id())
            ->where('week_start', $weekStart)
            ->first();

        if ($summary) {
            $summary->decrement('total_points', $dailyTask->xp_reward);
        }

        // Restar del total historico
        $userPoint = UserPoints::where('user_id', Auth::id())->first();
        if ($userPoint) {
            $userPoint->decrement('total_points', $dailyTask->xp_reward);
            $userPoint->update(['level' => $userPoint->calculateLevel()]);
        }

    }

    // Actualizar puntos totales, nivel y racha
    private function updateUserPoints(int $points): void
    {
        $userPoints = UserPoints::firstOrCreate(
            ['user_id' => Auth::id()],
            ['total_points' => 0, 'level' => 1, 'streak_days' => 0]
        );

        $userPoints->increment('total_points', $points);
        $userPoints->update([
            'level' => $userPoints->fresh()->calculateLevel(),
            'last_active' => Carbon::today(),
            'streak_days' => $this->calculateStreak($userPoints),
        ]);
    }

    // Calcular racha de dias consecutivos
    private function calculateStreak(UserPoints $userPoints): int
    {
        /** @var Carbon $lastActive */
        $lastActive = $userPoints->last_active;

        if (!$lastActive) {
            return 1;
        }

        // Si ya completo una tarea hoy, mantener la racha actual
        if ($lastActive->isToday()) {
            return $userPoints->streak_days;
        }

        // Si fue ayer, incrementar racha
        if ($lastActive->isYesterday()) {
            return $userPoints->streak_days + 1;
        }

        // Si fue hace mas de un dia, reiniciar racha
        return 1;
    }

    // Verifica que la tarea pertenece al usuario autenticado 
    private function checkOwner(DailyTask $dailyTask): void
    {
        if ($dailyTask->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para realizar esta acción .');
        }
    }

}
<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\PointLog;
use App\Models\UserPoints;
use Illuminate\Support\Facades\Auth;

class PointService
{
    // Sumar puntos
    public function addPoints(
        string $userId,
        int $amount,
        string $type,
        string $description,
        ?string $goalId = null,
        ?string $goalTaskId = null,
    ): void {
        // Actualizar balance
        $userPoints = UserPoints::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
        );
        $userPoints->addPoints($amount);

        // Registrar en el log
        PointLog::create([
            'user_id' => $userId,
            'goal_id' => $goalId,
            'goal_task_id' => $goalTaskId,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
        ]);
    }

    // Restar puntos
    public function subtractPoints(
        string $userId,
        int $amount,
        string $type,
        string $description,
        ?string $goalId = null,
        ?string $goalTaskId = null,
    ): void {
        $userPoints = UserPoints::where('user_id', $userId)->firstOrFail();
        $userPoints->refresh();
        $userPoints->spendPoints($amount);

        // Registrar en el log con amount negativo
        PointLog::create([
            'user_id' => $userId,
            'goal_id' => $goalId,
            'goal_task_id' => $goalTaskId,
            'amount' => -$amount,
            'type' => $type,
            'description' => $description,
        ]);
    }

    //Otorgar bonus al completar la meta 
    public function grantStreakBonus(Goal $goal): void
    {
        if ($goal->bonus_granted)
            return;

        $bonusTotal = Goal::bonusPoints($goal->term);
        $missedDays = $goal->missed_days;

        // Calcular porcentaje del bonus
        $bonusAmount = match (true) {
            $missedDays === 0 => $bonusTotal,
            $missedDays <= 2 => (int) ($bonusTotal * 0.5),
            default => 0,
        };

        if ($bonusAmount > 0) {
            $percentage = $missedDays === 0 ? '100%' : '50%';

            $this->addPoints(
                userId: $goal->user_id,
                amount: $bonusAmount,
                type: 'streak_bonus',
                description: "Bonus de racha ({$percentage}) — Meta completada: '{$goal->title}'",
                goalId: $goal->id,
            );
        }

        // Marcar como completada y bonus otorgado
        $goal->update([
            'status' => 'completed',
            'bonus_granted' => true,
        ]);
    }

    // Obtener balance del usuario
    public function getBalance(?string $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return UserPoints::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
        );
    }

    // Obtener historial de puntos
    public function getLogs(?string $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return PointLog::where('user_id', $userId)
            ->with(['goal', 'goalTask'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
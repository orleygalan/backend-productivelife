<?php

namespace App\Services;

use App\Models\Reward;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function __construct(private PointService $pointService)
    {
    }

    // Listar recompensas
    public function getAll()
    {
        return Reward::where('user_id', Auth::id())
            ->orderBy('points_cost', 'asc')
            ->get();
    }

    // Crear recompensa
    public function store(array $data)
    {
        return Reward::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'points_cost' => $data['points_cost'],
        ]);
    }

    // Actualizar recompensa
    public function update(Reward $reward, array $data)
    {
        $reward->update($data);
        return $reward->fresh();
    }

    // Eliminar recompensa
    public function destroy(Reward $reward): void
    {
        $reward->delete();
    }

    // Canjear recompensa
    public function redeem(Reward $reward): void
    {
        // Solo los domingos
        if (!Carbon::today()->isSunday()) {
            abort(422, 'Las recompensas solo se pueden canjear los domingos.');
        }

        $userId = Auth::id();
        $userPoints = $this->pointService->getBalance($userId);

        // Verificar saldo suficiente
        if (!$userPoints->hasEnough($reward->points_cost)) {
            abort(422, "No tienes suficientes puntos. Necesitas {$reward->points_cost} y tienes {$userPoints->balance}.");
        }

        DB::transaction(function () use ($reward, $userId) {
            $this->pointService->subtractPoints(
                userId: $userId,
                amount: $reward->points_cost,
                type: 'reward_redeem',
                description: "Recompensa canjeada: '{$reward->name}'",
            );
        });
    }

}
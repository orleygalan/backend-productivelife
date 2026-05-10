<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reward\StoreRewardRequest;
use App\Http\Requests\Reward\UpdateRewardRequest;
use App\Http\Resources\RewardResource;
use App\Models\Reward;
use App\Services\RewardService;

class RewardController extends Controller
{
    public function __construct(
        private RewardService $rewardService,
    ) {
    }

    // GET /api/rewards
    public function index()
    {
        $rewards = $this->rewardService->getAll();
        return response()->json(RewardResource::collection($rewards));
    }

    // POST /api/rewards
    public function store(StoreRewardRequest $request)
    {
        $reward = $this->rewardService->store($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Recompensa creada correctamente.',
            'data' => new RewardResource($reward),
        ], 201);
    }

    // PUT /api/rewards/{reward}
    public function update(UpdateRewardRequest $request, Reward $reward)
    {
        $this->authorize('update', $reward);
        $reward = $this->rewardService->update($reward, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Recompensa actualizada correctamente.',
            'data' => new RewardResource($reward),
        ]);
    }

    // DELETE /api/rewards/{reward}
    public function destroy(Reward $reward)
    {
        $this->authorize('delete', $reward);
        $this->rewardService->destroy($reward);
        return response()->json([
            'status' => 'success',
            'message' => 'Recompensa eliminada correctamente.',
        ]);
    }

    // POST /api/rewards/{reward}/redeem
    public function redeem(Reward $reward)
    {
        $this->authorize('redeem', $reward);
        $this->rewardService->redeem($reward);
        return response()->json([
            'status' => 'success',
            'message' => "Recompensa '{$reward->name}' canjeada correctamente.",
        ]);
    }
}
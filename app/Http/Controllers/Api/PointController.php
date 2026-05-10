<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PointLogResource;
use App\Http\Resources\UserPointsResource;
use App\Services\PointService;

class PointController extends Controller
{
    public function __construct(
        private PointService $pointService,
    ) {
    }

    // GET /api/points/balance
    public function balance()
    {
        $balance = $this->pointService->getBalance();
        return response()->json(new UserPointsResource($balance));
    }

    // GET /api/points/logs
    public function logs()
    {
        $logs = $this->pointService->getLogs();
        return response()->json(PointLogResource::collection($logs));
    }
}
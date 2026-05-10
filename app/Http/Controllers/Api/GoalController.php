<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goal\AddTaskGoalRequest;
use App\Http\Requests\Goal\StoreGoalRequest;
use App\Http\Requests\Goal\UpdateGoalRequest;
use App\Http\Requests\Goal\UpdateTaskGoalRequest;
use App\Http\Resources\GoalResource;
use App\Http\Resources\GoalTaskResource;
use App\Models\Goal;
use App\Models\GoalTask;
use App\Services\GoalService;
use App\Services\DailyCompletionService;
use App\Services\PointService;

class GoalController extends Controller
{
    public function __construct(
        private GoalService $goalService,
        private DailyCompletionService $dailyCompletionService,
        private PointService $pointService,
    ) {
    }

    // GET /api/goals
    public function index()
    {
        $goals = $this->goalService->getAll();
        return response()->json(GoalResource::collection($goals));
    }

    // POST /api/goals
    public function store(StoreGoalRequest $request)
    {
        $goal = $this->goalService->store($request->all());

        return response()->json([
            'status' => 'success',
            'message' => "Meta creada como " . Goal::termLabel($goal->term) . ".",
            'data' => new GoalResource($goal),
        ], 201);
    }

    // GET /api/goals/{goal}
    public function show(Goal $goal)
    {
        $this->authorize('view', $goal);
        $goal = $this->goalService->show($goal);
        return response()->json(new GoalResource($goal));
    }

    // PUT /api/goals/{goal}
    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        $this->authorize('update', $goal);
        $goal = $this->goalService->update($goal, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Meta actualizada correctamente.',
            'data' => new GoalResource($goal),
        ]);
    }

    // DELETE /api/goals/{goal}
    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);
        $this->goalService->destroy($goal);
        return response()->json([
            'status' => 'success',
            'message' => 'Meta eliminada correctamente.',
        ]);
    }

    // POST /api/goals/{goal}/tasks
    public function addTask(AddTaskGoalRequest $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $task = $this->goalService->addTask($goal, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea agregada correctamente.',
            'data' => new GoalTaskResource($task),
        ], 201);
    }

    // PUT /api/goals/{goal}/tasks/{task}
    public function updateTask(UpdateTaskGoalRequest $request, Goal $goal, GoalTask $task)
    {
        $this->authorize('update', $goal);

        $task = $this->goalService->updateTask($goal, $task, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea actualizada correctamente.',
            'data' => new GoalTaskResource($task),
        ]);
    }

    // DELETE /api/goals/{goal}/tasks/{task}
    public function deleteTask(Goal $goal, GoalTask $task)
    {
        $this->authorize('delete', $goal);
        $this->goalService->deleteTask($goal, $task);
        return response()->json([
            'status' => 'success',
            'message' => 'Tarea eliminada correctamente.',
        ]);
    }

    // GET /api/goals/{goal}/today
    public function today(Goal $goal)
    {
        $this->authorize('view', $goal);
        $tasks = $this->dailyCompletionService->getTodayTasks($goal);
        $points = $this->pointService->getBalance();

        return response()->json([
            'goal' => new GoalResource($goal->load('tasks')),
            'tasks' => GoalTaskResource::collection($tasks),
            'balance' => $points->balance,
        ]);
    }

    // POST /api/goals/{goal}/tasks/{task}/complete
    public function complete(Goal $goal, GoalTask $task)
    {
        $this->authorize('complete', $goal);
        $completion = $this->dailyCompletionService->complete($goal, $task);

        return response()->json([
            'status' => 'success',
            'message' => "+{$task->xp_per_day} XP ganados.",
            'data' => $completion,
        ]);
    }

    // DELETE /api/goals/{goal}/tasks/{task}/complete
    public function uncomplete(Goal $goal, GoalTask $task)
    {
        $this->authorize('uncomplete', $goal);
        $this->dailyCompletionService->uncomplete($goal, $task);

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea desmarcada correctamente.',
        ]);
    }
}
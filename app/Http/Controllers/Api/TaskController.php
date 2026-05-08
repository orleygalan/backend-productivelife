<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function __construct(private TaskService $taskService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    // GET /api/projects/{project}/tasks
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $tasks = $this->taskService->getAll($project);
        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    // POST /api/tasks
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $project = Project::findOrFail($request->validated()['project_id']);
        $this->authorize('view', $project);

        $task = $this->taskService->store($request->validated());
        return response()->json(new TaskResource($task), 201);
    }

    /**
     * Display the specified resource.
     */
    // GET /api/tasks/{task}
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);
        $task = $this->taskService->show($task);
        return response()->json(new TaskResource($task));
    }

    /**
     * Update the specified resource in storage.
     */
    // PUT /api/tasks/{task}
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);
        $task = $this->taskService->update($task, $request->validated());
        return response()->json(new TaskResource($task));
    }

    /**
     * Remove the specified resource from storage.
     */
    // DELETE /api/tasks/{task}
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('manage', $task->project);
        $this->taskService->destroy($task);
        return response()->json(['message' => 'Tarea eliminada correctamente .']);
    }

    // PATCH /api/tasks/{task}/status
    public function changeStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);

        $request->validate([
            'status' => ['required', 'in:todo,in_progress,done']
        ]);

        $task = $this->taskService->changeStatus($task, $request->status);
        return response()->json(new TaskResource($task));
    }
}

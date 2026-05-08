<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Team;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService)
    {
    }

    // GET /api/teams/{team}/projects
    public function index(Team $team): JsonResponse
    {
        $this->authorize('view', $team);
        $project = $this->projectService->getAll($team);
        return response()->json(ProjectResource::collection($project));
    }

    // POST /api/projects
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $team = Team::findOrFail($request->validated()['team_id']);
        $this->authorize('manage', $team);
        $project = $this->projectService->store($request->validated());
        return response()->json(new ProjectResource($project), 201);
    }

    // GET /api/projects/{project}
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project = $this->projectService->show($project);
        return response()->json(new ProjectResource($project));
    }

    // PUT /api/projects/{project}
    public function update(UpdateProjectRequest $data, Project $project): JsonResponse
    {
         $this->authorize('manage', $project);
        $project = $this->projectService->update($project, $data->validated());
        return response()->json(new ProjectResource($project));
    }

    // DELETE /api/projects/{project}
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('manage', $project);
        $this->projectService->destroy($project);
        return response()->json(['message' => 'Proyecto eliminado correctamente.']);
    }
}

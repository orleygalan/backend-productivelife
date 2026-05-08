<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Organization;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\TeamService;

class TeamController extends Controller
{

    public function __construct(private TeamService $teamService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    // GET /api/organizations/{organization}/teams
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);
        $team = $this->teamService->getAll($organization);
        return response()->json(TeamResource::collection($team));
    }

    /**
     * Store a newly created resource in storage.
     */
    // POST /api/teams
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $organization = Organization::findOrFail($request->validated()['organization_id']);

        $this->authorize('update', $organization);
        $team = $this->teamService->store($request->validated());
        return response()->json(new TeamResource($team), 201);
    }

    /**
     * Display the specified resource.
     */
    // GET /api/teams/{team}
    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);
        $team = $this->teamService->show($team);
        return response()->json(new TeamResource($team));
    }

    /**
     * Update the specified resource in storage.
     */
    // PUT /api/teams/{team}
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('manage', $team);
        $team = $this->teamService->update($team, $request->validated());
        return response()->json(new TeamResource($team));
    }

    /**
     * Remove the specified resource from storage.
     */
    // DELETE /api/teams/{team}
    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('manage', $team);
        $this->teamService->destroy($team);
        return response()->json(['message' => 'Equipo eliminado correctamente.']);
    }

    // POST /api/teams/{team}/members
    public function addMembers(Request $request, Team $team): JsonResponse
    {
        $this->authorize('manage', $team);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['sometimes', 'in:admin,editor,viewer'],
        ]);

        $this->teamService->addMember($team, $request->email, $request->role ?? 'editor');

        return response()->json(['message' => 'Miembro agregado correctamente.']);
    }

    // DELETE /api/teams/{team}/members/{user}
    public function removeMember(Team $team, string $userId): JsonResponse
    {
        $this->authorize('manage', $team);
        $this->teamService->removeMember($team, $userId);

        return response()->json(['message' => 'Miembro eliminado correctamente.']);
    }
}

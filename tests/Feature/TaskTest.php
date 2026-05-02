<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithMember(): array
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $organization->id]);
        $team->members()->attach($owner->id, ['role' => 'admin']);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return compact('owner', 'organization', 'team', 'project');
    }

    // Crear tarea
    public function test_member_can_create_task(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithMember();

        $response = $this->actingAs($owner)
            ->postJson('/api/tasks', [
                'title' => 'Nueva tarea',
                'project_id' => $project->id,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'status', 'project_id']);
        $response->assertJson(['title' => 'Nueva tarea', 'status' => 'todo']);
    }

    // Crear tarea sin ser miembro
    public function test_non_member_cannot_create_task(): void
    {
        ['project' => $project] = $this->createProjectWithMember();
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)
            ->postJson('/api/tasks', [
                'title' => 'Hack task',
                'project_id' => $project->id,
            ]);

        $response->assertStatus(403);
    }

    // Cambiar estado de tarea
    public function test_member_can_change_task_status(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithMember();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/tasks/{$task->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'in_progress']);
    }

    // Cambiar estado con valor invalido
    public function test_cannot_change_task_status_to_invalid_value(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithMember();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/tasks/{$task->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    // Eliminar tarea/soft delete
    public function test_admin_can_delete_task(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    // No miembro no puede eliminar tarea
    public function test_non_member_cannot_delete_task(): void
    {
        ['project' => $project] = $this->createProjectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }
}
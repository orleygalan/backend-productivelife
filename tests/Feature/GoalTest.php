<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\GoalTask;
use App\Models\User;
use App\Models\UserPoints;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    private function goalData(array $override = []): array
    {
        return array_merge([
            'title' => 'Aprender inglés',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'tasks' => [
                ['title' => 'Estudiar vocabulario', 'xp_per_day' => 150],
                ['title' => 'Practicar pronunciación', 'xp_per_day' => 100],
            ],
        ], $override);
    }

    public function test_user_can_create_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/goals', $this->goalData());

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Aprender inglés'])
            ->assertJsonFragment(['term' => 'short']);
    }

    public function test_goal_requires_at_least_two_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/goals', $this->goalData([
                'tasks' => [
                    ['title' => 'Solo una tarea', 'xp_per_day' => 150],
                ],
            ]));

        $response->assertStatus(422);
    }

    public function test_xp_out_of_range_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/goals', $this->goalData([
                'tasks' => [
                    ['title' => 'Tarea 1', 'xp_per_day' => 500],
                    ['title' => 'Tarea 2', 'xp_per_day' => 150],
                ],
            ]));

        $response->assertStatus(422);
    }

    public function test_goal_term_is_calculated_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/goals', [
                'title' => 'Meta mediano plazo',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addYears(2)->toDateString(),
                'tasks' => [
                    ['title' => 'Tarea 1', 'xp_per_day' => 400],
                    ['title' => 'Tarea 2', 'xp_per_day' => 350],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['term' => 'medium']);
    }

    public function test_user_only_sees_their_goals(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Goal::factory()->count(2)->create(['user_id' => $user->id]);
        Goal::factory()->count(3)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/goals');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_user_cannot_delete_others_goal(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/goals/{$goal->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_complete_task(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'term' => 'short',
        ]);
        $task = GoalTask::factory()->create([
            'goal_id' => $goal->id,
            'xp_per_day' => 150,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('daily_completions', [
            'goal_task_id' => $task->id,
            'user_id' => $user->id,
            'xp_earned' => 150,
        ]);
    }

    public function test_cannot_complete_task_twice_same_day(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'term' => 'short',
        ]);
        $task = GoalTask::factory()->create(['goal_id' => $goal->id]);

        $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $response = $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $response->assertStatus(422);
    }

    public function test_completing_task_adds_points_to_balance(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'term' => 'short',
        ]);
        $task = GoalTask::factory()->create([
            'goal_id' => $goal->id,
            'xp_per_day' => 200,
        ]);

        $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $this->assertDatabaseHas('user_points', [
            'user_id' => $user->id,
            'balance' => 200,
        ]);
    }

    public function test_uncompleting_task_removes_points(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'term' => 'short',
        ]);
        $task = GoalTask::factory()->create([
            'goal_id' => $goal->id,
            'xp_per_day' => 200,
        ]);

        $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $this->actingAs($user)
            ->deleteJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $this->assertDatabaseHas('user_points', [
            'user_id' => $user->id,
            'balance' => 0,
        ]);
    }

    public function test_task_is_editable_only_on_creation_day(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        // Tarea creada ayer
        $task = GoalTask::factory()->create([
            'goal_id' => $goal->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/goals/{$goal->id}/tasks/{$task->id}", [
                'title' => 'Intento editar',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_create_reward(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/rewards', [
                'name' => 'Salir a cenar',
                'points_cost' => 5000,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Salir a cenar']);
    }

    public function test_user_can_redeem_reward_on_sunday(): void
    {
        Carbon::setTestNow(Carbon::parse('next sunday'));

        $user = User::factory()->create();
        $reward = \App\Models\Reward::factory()->create([
            'user_id' => $user->id,
            'points_cost' => 1000,
        ]);

        UserPoints::create([
            'user_id' => $user->id,
            'balance' => 5000,
            'total_earned' => 5000,
            'total_spent' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/rewards/{$reward->id}/redeem");

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_points', [
            'user_id' => $user->id,
            'balance' => 4000,
        ]);

        Carbon::setTestNow();
    }

    public function test_cannot_redeem_on_weekday(): void
    {
        Carbon::setTestNow(Carbon::parse('next monday'));

        $user = User::factory()->create();
        $reward = \App\Models\Reward::factory()->create([
            'user_id' => $user->id,
            'points_cost' => 100,
        ]);

        UserPoints::create([
            'user_id' => $user->id,
            'balance' => 5000,
            'total_earned' => 5000,
            'total_spent' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/rewards/{$reward->id}/redeem");

        $response->assertStatus(422);

        Carbon::setTestNow();
    }

    public function test_cannot_redeem_without_enough_points(): void
    {
        Carbon::setTestNow(Carbon::parse('next sunday'));

        $user = User::factory()->create();
        $reward = \App\Models\Reward::factory()->create([
            'user_id' => $user->id,
            'points_cost' => 10000,
        ]);

        UserPoints::create([
            'user_id' => $user->id,
            'balance' => 500,
            'total_earned' => 500,
            'total_spent' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/rewards/{$reward->id}/redeem");

        $response->assertStatus(422);

        Carbon::setTestNow();
    }

    public function test_user_can_get_balance(): void
    {
        $user = User::factory()->create();

        UserPoints::create([
            'user_id' => $user->id,
            'balance' => 1500,
            'total_earned' => 2000,
            'total_spent' => 500,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/points/balance');

        $response->assertStatus(200)
            ->assertJsonFragment(['balance' => 1500]);
    }

    public function test_user_can_get_point_logs(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'term' => 'short',
        ]);
        $task = GoalTask::factory()->create([
            'goal_id' => $goal->id,
            'xp_per_day' => 150,
        ]);

        $this->actingAs($user)
            ->postJson("/api/goals/{$goal->id}/tasks/{$task->id}/complete");

        $response = $this->actingAs($user)
            ->getJson('/api/points/logs');

        $response->assertStatus(200)
            ->assertJsonFragment(['type' => 'daily_task']);
    }
}
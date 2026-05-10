<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_team(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/teams', [
                'name' => 'Equipo Alpha',
                'organization_id' => $org->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Equipo Alpha']);
    }

    public function test_creator_is_added_as_admin(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)->postJson('/api/teams', [
            'name' => 'Equipo Alpha',
            'organization_id' => $org->id,
        ]);

        $team = Team::where('name', 'Equipo Alpha')->first();

        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_owner_can_add_member_by_email(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($owner)
            ->postJson("/api/teams/{$team->id}/members", [
                'email' => $member->email,
                'role' => 'editor',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'editor',
        ]);
    }

    public function test_cannot_add_nonexistent_user(): void
    {
        $owner = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($owner)
            ->postJson("/api/teams/{$team->id}/members", [
                'email' => 'noexiste@test.com',
                'role' => 'editor',
            ]);

        $response->assertStatus(422);
    }

    public function test_member_can_see_their_teams(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $team->members()->attach($member->id, ['role' => 'editor']);

        $response = $this->actingAs($member)
            ->getJson("/api/organizations/{$org->id}/teams");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_member_can_see_organization(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $team->members()->attach($member->id, ['role' => 'editor']);

        $response = $this->actingAs($member)
            ->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $org->id]);
    }

    public function test_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $owner->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $team->members()->attach($member->id, ['role' => 'editor']);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/teams/{$team->id}/members/{$member->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('team_members', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }
}
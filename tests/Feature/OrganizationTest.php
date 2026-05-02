<?php
namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    // Crear organizacion 
    public function test_user_can_create_organization(): void
    {

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/organizations', [
                'name' => 'FactusGal'
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name', 'owner_id', 'created_at']);
    }

    // Crear organizacion sin autenticacion 
    public function test_unauthenticated_user_cannot_create_organization(): void
    {
        $response = $this->postJson('/api/organizations', [
            'name' => 'FactusGal'
        ]);

        $response->assertStatus(401);
    }

    // Ver lista de organizaciones 
    public function test_user_can_list_their_organizations(): void
    {
        $user = User::factory()->create();
        Organization::factory()->count(3)->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/organizations');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    // ver una organizacion propia 
    public function test_user_can_view_own_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/organizations/{$organization->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $organization->id]);
    }

    // Ver organizacion ajena/Policy
    public function test_user_cannot_view_other_users_organization(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $other->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/organizations/{$organization->id}");

        $response->assertStatus(403);
        $response->assertJson(['status' => 'error']);
    }

    // Actualizar organizacion propia
    public function test_user_can_update_own_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/organizations/{$organization->id}", [
                'name' => 'FactuGalPro',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['name' => 'FactuGalPro']);
    }

    // Actualizar organizacion ajena
    public function test_user_cannot_update_other_users_organization(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $other->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/organizations/{$organization->id}", [
                'name' => 'Hack',
            ]);

        $response->assertStatus(403);
    }

    // Eliminar organizacion propia/soft delete
    public function test_user_can_delete_own_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/organizations/{$organization->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
    }

    // Eliminar organizacion ajena
    public function test_user_cannot_delete_other_users_organization(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $other->id]);

        $response = $this->actingAs($user)
        ->deleteJson("/api/organizations/{$organization->id}");
        
        $response->assertStatus(403);
    }
}
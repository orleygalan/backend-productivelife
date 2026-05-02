<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Registro exitoso 
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Manuel',
            'email' => 'manuel@gmail.com',
            'password' => 'Manuel123',
            'password_confirmation' => 'Manuel123'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'mode'],
            'token',
        ]);
    }

    // Registrar con email duplicado
    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'manuel@gmail.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Manuel',
            'email' => 'manuel@gmail.com',
            'password' => 'Manuel123',
            'password_confirmation' => 'Manuel123'
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'message', 'errors']);
    }

    // Registrar con datos incompletos 
    public function test_user_cannot_register_with_missing_fields(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Manuel',
        ]);

        $response->assertStatus(422);
    }

    // Login exitoso 
    public function test_user_can_login(): void
    {

        User::factory()->create([
            'email' => 'manuel@gmail.com',
            'password' => bcrypt('manuel123*'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'manuel@gmail.com',
            'password' => 'manuel123*'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
    }

    // Login con credenciales incorrectas 
    public function test_user_cannot_login_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'manuel@gmail.com',
            'password' => bcrypt('manuel123*'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'manuel@gmail.com',
            'password' => 'manueAguilar12'
        ]);

        $response->assertStatus(401);
        $response->assertJson(['status' => 'error']);
    }

    // Logout exitoso 
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sesión cerrada correctamente.']);
    }

    // Logout sin autenticacion 
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    // Ver perfl 
    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/auth/profile');

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'email', 'mode']);
    }
}

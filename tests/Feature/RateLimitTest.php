<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limit_blocks_after_5_attempts(): void
    {
        // Hacer 5 intentos fallidos
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/auth/login', [
                'email' => 'manuel@gmail.com',
                'password' => 'wrongpassword'
            ]);
        }

        // El sexto intento debe ser bloqueado
        $response = $this->postJson('/api/auth/login', [
            'email' => 'manuel@gmail.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Demasiados intentos. Espera un minuto.'
        ]);
    }

    public function test_api_rate_limit_blocks_after_60_requests(): void
    {
        $user = User::factory()->create();

        // 60 requests
        foreach (range(1, 60) as $attempt) {
            $this->actingAs($user)
                ->getJson('/api/auth/profile');
        }

        // El 61 debe ser bloqueado
        $response = $this->actingAs($user)
            ->getJson('/api/auth/profile');

        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Demasiados intentos. Espera un minuto.'
        ]);
    }
}

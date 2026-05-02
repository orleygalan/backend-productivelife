<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserPoints;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    // Registrar usuario
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'mode' => 'life',
        ]);

        UserPoints::create([
            'user_id' => $user->id,
            'total_points' => 0,
            'level' => 1,
            'streak_days' => 0,
        ]);

        // Generar token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
        ];
    }

    // Login
    public function login(array $data): array
    {
        // Verificar credenciales
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw new \Exception('Credenciales incorrectas.');
        }

        $user = Auth::user();

        // Revocar tokens anteriores para no acumular
        $user->tokens()->delete();

        // Generar nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => new UserResource($user->load('points')),
            'token' => $token,
        ];
    }

    // Logout
    public function logout(): void
    {
        $token = Auth::user()->currentAccessToken();
        if (method_exists($token, 'delete')) {
            $token->delete();
        }
    }

    // Cambiar modo work | life
    public function switchMode(string $mode): UserResource
    {
        $user = Auth::user();
        $user->update(['mode' => $mode]);
        return new UserResource($user->fresh());
    }

    // Perfil del usuario autenticado
    public function profile(): UserResource
    {
        return new UserResource(Auth::user()->load('points'));
    }
}
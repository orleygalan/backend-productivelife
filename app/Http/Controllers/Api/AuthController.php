<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    // POST /api/auth/register
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register($request->validated());

        return response()->json([
            'message' => '¡Registro exitoso!',
            'user' => $data['user'],
            'token' => $data['token'],
        ], 201);
    }

    // POST /api/auth/login
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());

            return response()->json([
                'message' => '¡Bienvenido!',
                'user' => $data['user'],
                'token' => $data['token'],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    // POST /api/auth/logout
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    // PATCH /api/auth/mode
    public function switchMode(Request $request): JsonResponse
    {
        $request->validate([
            'mode' => ['required', 'in:work,life'],
        ]);

        $user = $this->authService->switchMode($request->mode);

        return response()->json([
            'message' => 'Modo cambiado correctamente.',
            'user' => $user,
        ]);
    }

    // GET /api/auth/profile
    public function profile(): JsonResponse
    {
        $user = $this->authService->profile();
        return response()->json($user);
    }
}
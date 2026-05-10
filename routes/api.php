<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

// Publico 
Route::middleware('throttle:auth')->prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protegido
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::patch('mode', [AuthController::class, 'switchMode']);
        Route::get('profile', [AuthController::class, 'profile']);
    });

    // Modo Work 
    Route::apiResource('organizations', OrganizationController::class);

    // Equipos 
    Route::apiResource('teams', TeamController::class);
    Route::get('organizations/{organization}/teams', [TeamController::class, 'index']);
    Route::post('teams/{team}/members', [TeamController::class, 'addMembers']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    // Proyectos 
    Route::apiResource('projects', ProjectController::class);
    Route::get('teams/{team}/projects', [ProjectController::class, 'index']);

    // Tarea 
    Route::apiResource('tasks', TaskController::class);
    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'changeStatus']);

    // Modo life 

    Route::prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/', [GoalController::class, 'store']);

        Route::prefix('{goal}')->group(function () {
            Route::get('/', [GoalController::class, 'show']);
            Route::put('/', [GoalController::class, 'update']);
            Route::delete('/', [GoalController::class, 'destroy']);

            // Ver tareas del dia de esta meta
            Route::get('/today', [GoalController::class, 'today']);

            // Tareas de la meta
            Route::post('/tasks', [GoalController::class, 'addTask']);
            Route::put('/tasks/{task}', [GoalController::class, 'updateTask']);
            Route::delete('/tasks/{task}', [GoalController::class, 'deleteTask']);

            // Completar / descompletar tarea diaria
            Route::post('/tasks/{task}/complete', [GoalController::class, 'complete']);
            Route::delete('/tasks/{task}/complete', [GoalController::class, 'uncomplete']);
        });
    });

    // Puntos
    Route::prefix('points')->group(function () {
        Route::get('/balance', [PointController::class, 'balance']);
        Route::get('/logs', [PointController::class, 'logs']);
    });

    // Recompensas
        Route::apiResource('rewards', RewardController::class);
        Route::post('rewards/{reward}/redeem', [RewardController::class, 'redeem']);
});

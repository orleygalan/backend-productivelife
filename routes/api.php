<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyTaskController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\WeeklyPointsController;
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
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    // Proyectos 
    Route::apiResource('projects', ProjectController::class);
    Route::get('teams/{team}/projects', [ProjectController::class, 'index']);

    // Tarea 
    Route::apiResource('tasks', TaskController::class);
    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'changeStatus']);

    // Modo life 

    // Daily-task 
    Route::apiResource('daily-tasks', DailyTaskController::class)->except(['show']);
    Route::patch('daily-tasks/{daily-task}/complete', [DailyTaskController::class, 'complete']);
    Route::patch('daily-tasks/{daily-task}/uncomplete', [DailyTaskController::class, 'uncomplete']);

    // Reward 
    Route::apiResource('rewards', RewardController::class)->except(['show']);
    Route::post('rewards/{reward}/redeem', [RewardController::class, 'redeem']);
    Route::get('rewards/redemptions', [RewardController::class, 'redemptions']);

    // weekly-points
    Route::get('weekly-points/current', [WeeklyPointsController::class, 'current']);
    Route::get('weekly-points/history', [WeeklyPointsController::class, 'history']);
});

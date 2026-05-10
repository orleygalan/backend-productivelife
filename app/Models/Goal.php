<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'term',
        'status',
        'current_streak',
        'max_streak',
        'missed_days',
        'bonus_granted',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'bonus_granted' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(GoalTask::class);
    }

    public function dailyCompletions()
    {
        return $this->hasMany(DailyCompletion::class);
    }

    public function pointLogs()
    {
        return $this->hasMany(PointLog::class);
    }

    // Calcula el term segun la diferencia de fechas
    public static function calculateTerm(string $startDate, string $endDate): string
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $years = $start->diffInYears($end);
        $months = $start->diffInMonths($end);

        if ($months < 12) {
            return 'short';
        } elseif ($years < 5) {
            return 'medium';
        } else {
            return 'long';
        }
    }

    // Rango de XP permitido según el term
    public static function xpRange(string $term): array
    {
        return match ($term) {
            'short' => ['min' => 100, 'max' => 300],
            'medium' => ['min' => 301, 'max' => 500],
            'long' => ['min' => 501, 'max' => 700],
        };
    }

    // Bonus de puntos al completar la meta
    public static function bonusPoints(string $term): int
    {
        return match ($term) {
            'short' => 500,
            'medium' => 1500,
            'long' => 5000,
        };
    }

    // Label legible del term
    public static function termLabel(string $term): string
    {
        return match ($term) {
            'short' => 'Corto plazo',
            'medium' => 'Mediano plazo',
            'long' => 'Largo plazo',
        };
    }

    // Verifica si la meta ya venció
    public function isExpired(): bool
    {
        return Carbon::today()->isAfter($this->end_date);
    }

    // Verifica si hoy es el último día de la meta
    public function isLastDay(): bool
    {
        return Carbon::today()->isSameDay($this->end_date);
    }

    // Obtiene las completions del día de hoy
    public function todayCompletions()
    {
        return $this->dailyCompletions()
            ->where('completed_date', Carbon::today()->toDateString())
            ->get();
    }

    // Verifica si todas las tareas del día de hoy fueron completadas
    public function allTasksCompletedToday(): bool
    {
        $totalTasks = $this->tasks()->count();
        $completedToday = $this->dailyCompletions()
            ->where('completed_date', Carbon::today()->toDateString())
            ->count();

        return $totalTasks > 0 && $completedToday === $totalTasks;
    }

}
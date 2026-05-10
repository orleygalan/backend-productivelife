<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalTask extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'goal_id',
        'title',
        'xp_per_day',
    ];


    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function dailyCompletions()
    {
        return $this->hasMany(DailyCompletion::class);
    }


    // Verifica si esta tarea fue completada hoy
    public function isCompletedToday(string $userId): bool
    {
        return $this->dailyCompletions()
            ->where('user_id', $userId)
            ->whereDate('completed_date', Carbon::today()->toDateString())
            ->exists();
    }

    // Verifica si la tarea se puede modificar o eliminar
    // Solo el mismo dia de su creacion
    public function isEditable(): bool
    {
        return Carbon::today()->isSameDay($this->created_at);
    }
}
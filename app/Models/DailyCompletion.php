<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyCompletion extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'goal_task_id',
        'goal_id',
        'user_id',
        'completed_date',
        'xp_earned',
    ];

    protected $casts = [
        'completed_date' => 'date',
    ];
    

    public function goalTask()
    {
        return $this->belongsTo(GoalTask::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
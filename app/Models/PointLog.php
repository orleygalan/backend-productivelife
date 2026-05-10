<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointLog extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'goal_task_id',
        'amount',
        'type',
        'description',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function goalTask()
    {
        return $this->belongsTo(GoalTask::class);
    }
}
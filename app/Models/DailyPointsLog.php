<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DailyPointsLog extends Model
{
    use HasUuids;

    protected $table = 'daily_points_log';
    protected $fillable = [
        'points_earned',
        'log_date',
        'user_id',
        'daily_task_id',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }
}

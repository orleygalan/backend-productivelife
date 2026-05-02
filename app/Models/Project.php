<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'team_id'
    ];

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    // Tareas agrupadas por estado para el Kanban
    public function taskByStatus(){
        return $this->tasks()->get()->groupBy('status');
    }
}

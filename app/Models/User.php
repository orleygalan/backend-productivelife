<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuids, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mode'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // mode work 

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignedTask()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // mode life 

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function dailyCompletions()
    {
        return $this->hasMany(DailyCompletion::class);
    }

    public function pointLogs()
    {
        return $this->hasMany(PointLog::class);
    }

    public function points()
    {
        return $this->hasOne(UserPoints::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    // obtiene o crea el balance del usuario
    public function getOrCreatePoints(): UserPoints
    {
        return $this->points()->firstOrCreate(
            ['user_id' => $this->id],
            ['balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
        );
    }

}

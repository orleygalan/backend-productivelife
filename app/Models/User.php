<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuids;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory, Notifiable;

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

    public function assignedTask(){
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // mode life 

    public function dailyTasks(){
        return $this->hasMany(DailyTask::class);
    }

    public function dailyPointsLog(){
        return $this->hasMany(DailyPointsLog::class);
    }

    public function weeklyPointsSummary(){
        return $this->hasMany(WeeklyPointsSummary::class);
    }

    public function points(){
        return $this->hasOne(UserPoints::class);
    }

    public function rewards(){
        return $this->hasMany(Reward::class);
    }

    public function redemptions(){
        return $this->hasMany(RewardRedemption::class);
    }

}

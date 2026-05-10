<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserPoints extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_spent',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Sumar puntos ganados
    public function addPoints(int $amount): void
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
    }

    // Restar puntos gastados
    public function spendPoints(int $amount): void
    {
        $this->decrement('balance', $amount);
        $this->increment('total_spent', $amount);
    }

    // Verifica si tiene suficientes puntos
    public function hasEnough(int $amount): bool
    {
        return $this->balance >= $amount;
    }
}
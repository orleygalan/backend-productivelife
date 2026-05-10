<?php
namespace App\Policies;

use App\Models\Reward;
use App\Models\User;

class RewardPolicy
{

    public function update(User $user, Reward $reward): bool
    {
        return $reward->user_id === $user->id;
    }

    public function delete(User $user, Reward $reward): bool
    {
        return $reward->user_id === $user->id;
    }

    public function redeem(User $user, Reward $reward): bool
    {
        return $reward->user_id === $user->id;
    }
}
<?php

namespace Database\Factories;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RewardFactory extends Factory
{
    protected $model = Reward::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(2),
            'points_cost' => $this->faker->numberBetween(1000, 10000),
        ];
    }
}
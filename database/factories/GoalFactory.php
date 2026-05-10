<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    protected $model = Goal::class;

    public function definition(): array
    {
        $start = now();
        $end = now()->addMonths(6);

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'term' => 'short',
            'status' => 'active',
            'current_streak' => 0,
            'max_streak' => 0,
            'missed_days' => 0,
            'bonus_granted' => false,
        ];
    }
}
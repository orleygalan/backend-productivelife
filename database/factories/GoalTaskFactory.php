<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\GoalTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalTaskFactory extends Factory
{
    protected $model = GoalTask::class;

    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'title' => $this->faker->sentence(3),
            'xp_per_day' => $this->faker->numberBetween(100, 300),
        ];
    }
}
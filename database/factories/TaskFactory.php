<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => 'todo',
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'project_id' => Project::factory(),
            'assigned_to' => null,
        ];
    }
}
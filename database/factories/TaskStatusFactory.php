<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskStatus>
 */
class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'position' => fake()->numberBetween(0, 10),
            'is_default' => fake()->boolean(),
            'is_closed' => fake()->boolean(),
        ];
    }
}

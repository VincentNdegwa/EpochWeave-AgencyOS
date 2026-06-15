<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use App\Models\Workspace;
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
            'workspace_id' => Workspace::factory(),
            'title' => fake()->word(),
            'color' => fake()->hexColor(),
            'is_system' => false,
            'automation_trigger' => null,
            'position' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Create a system status.
     */
    public function system(string $trigger, string $title, string $color, int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
            'color' => $color,
            'is_system' => true,
            'automation_trigger' => $trigger,
            'position' => $position,
        ]);
    }
}

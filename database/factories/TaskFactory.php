<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'project_id' => Project::factory(),
            'task_status_id' => TaskStatus::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'assignee_id' => User::factory(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'position' => fake()->numberBetween(0, 9999),
            'start_date' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'estimated_hours' => fake()->optional()->randomFloat(2, 1, 40),
            'is_billable' => true,
            'completed_at' => null,
            'created_by' => User::factory(),
        ];
    }
}

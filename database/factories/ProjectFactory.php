<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'account_id' => Account::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'color' => fake()->hexColor(),
            'status' => fake()->randomElement(['active', 'on_hold', 'completed', 'archived']),
            'hourly_rate' => fake()->numberBetween(2_500, 20_000),
            'currency' => 'KES',
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
            'tasks_total' => 0,
            'tasks_completed' => 0,
            'hours_logged' => 0,
            'hours_budgeted' => fake()->randomFloat(2, 10, 120),
            'portal_visible' => true,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (Project $project): void {
                if ($project->account) {
                    $project->account->workspace_id = $project->workspace_id;
                }
            })
            ->afterCreating(function (Project $project): void {
                $project->account()->update(['workspace_id' => $project->workspace_id]);
            });
    }
}

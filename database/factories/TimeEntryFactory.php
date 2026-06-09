<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-3 days', 'now');
        $endedAt = (clone $startedAt)->modify('+'.fake()->numberBetween(1, 4).' hours');

        return [
            'workspace_id' => Workspace::factory(),
            'project_id' => Project::factory(),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'description' => fake()->sentence(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $endedAt->getTimestamp() - $startedAt->getTimestamp(),
            'is_billable' => true,
            'is_invoiced' => false,
            'hourly_rate' => fake()->randomFloat(2, 25, 200),
            'date' => $startedAt->format('Y-m-d'),
            'invoice_id' => null,
        ];
    }
}

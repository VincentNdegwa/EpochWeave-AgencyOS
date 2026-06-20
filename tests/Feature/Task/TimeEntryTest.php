<?php

namespace Tests\Feature\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);

        $project = Project::factory()->create(['workspace_id' => $this->workspace->id]);
        $status = TaskStatus::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->task = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $project->id,
            'task_status_id' => $status->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_user_can_create_time_entry()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('tasks.time-entries.store', $this->task), [
                'project_id' => $this->task->project_id,
                'description' => 'Working on feature',
                'started_at' => now()->subHour()->toIso8601String(),
                'ended_at' => now()->toIso8601String(),
                'is_billable' => true,
                'hourly_rate' => 100,
                'date' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_entries', [
            'task_id' => $this->task->id,
            'description' => 'Working on feature',
            'is_billable' => true,
        ]);
    }

    public function test_time_entry_duration_is_calculated()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('tasks.time-entries.store', $this->task), [
                'project_id' => $this->task->project_id,
                'started_at' => now()->subMinutes(30)->toIso8601String(),
                'ended_at' => now()->toIso8601String(),
                'date' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $entry = TimeEntry::query()->where('task_id', $this->task->id)->first();
        $this->assertNotNull($entry);
        $this->assertEquals(1800, $entry->duration_seconds);
    }

    public function test_user_can_update_time_entry()
    {
        $entry = TimeEntry::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->patch(route('tasks.time-entries.update', [$this->task, $entry]), [
                'project_id' => $this->task->project_id,
                'description' => 'Updated description',
                'started_at' => now()->subHour()->toIso8601String(),
                'ended_at' => now()->toIso8601String(),
                'date' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'description' => 'Updated description',
        ]);
    }

    public function test_user_can_delete_time_entry()
    {
        $entry = TimeEntry::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->delete(route('tasks.time-entries.destroy', [$this->task, $entry]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
}

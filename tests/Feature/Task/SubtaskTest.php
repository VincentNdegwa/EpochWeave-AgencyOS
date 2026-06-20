<?php

namespace Tests\Feature\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Task $parentTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);

        $project = Project::factory()->create(['workspace_id' => $this->workspace->id]);
        $status = TaskStatus::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->parentTask = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $project->id,
            'task_status_id' => $status->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_user_can_create_subtask()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('tasks.store'), [
                'project_id' => $this->parentTask->project_id,
                'title' => 'Subtask title',
                'parent_id' => $this->parentTask->id,
                'task_status_id' => $this->parentTask->task_status_id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Subtask title',
            'parent_id' => $this->parentTask->id,
            'workspace_id' => $this->workspace->id,
        ]);
    }

    public function test_task_show_includes_children()
    {
        $status = TaskStatus::factory()->create(['workspace_id' => $this->workspace->id]);
        Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->parentTask->project_id,
            'task_status_id' => $status->id,
            'parent_id' => $this->parentTask->id,
            'title' => 'Child task',
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->get(route('tasks.show', $this->parentTask));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('task.children')
        );
    }

    public function test_user_can_delete_subtask()
    {
        $status = TaskStatus::factory()->create(['workspace_id' => $this->workspace->id]);
        $child = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $this->parentTask->project_id,
            'task_status_id' => $status->id,
            'parent_id' => $this->parentTask->id,
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->delete(route('tasks.destroy', $child));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $child->id]);
    }
}

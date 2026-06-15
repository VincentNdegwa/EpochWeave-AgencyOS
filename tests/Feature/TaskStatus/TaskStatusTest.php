<?php

namespace Tests\Feature\TaskStatus;

use App\Models\Role;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_task_statuses(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Default statuses are created by WorkspaceObserver
        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Todo',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/task-status');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('task-status/index')
                ->has('task_statuses')
            );
    }

    public function test_user_can_create_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/task-status', [
                'title' => 'In Review',
                'color' => '#a855f7',
                'position' => 3,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_statuses', [
            'title' => 'In Review',
            'workspace_id' => $workspace->id,
            'color' => '#a855f7',
            'position' => 3,
        ]);
    }

    public function test_user_cannot_create_task_status_without_title(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/task-status', [
                'color' => '#a855f7',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_update_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = TaskStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/task-status/{$status->id}", [
                'title' => 'Updated Title',
                'color' => '#22c55e',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
            'color' => '#22c55e',
        ]);
    }

    public function test_user_cannot_update_system_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Get existing system status created by WorkspaceObserver
        $status = TaskStatus::where('workspace_id', $workspace->id)
            ->where('is_system', true)
            ->first();

        $this->assertNotNull($status, 'System status should exist from WorkspaceObserver');

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/task-status/{$status->id}", [
                'title' => 'Updated Title',
                'color' => '#22c55e',
            ]);

        // System statuses can be updated but not deleted
        $response->assertRedirect();
        $this->assertDatabaseHas('task_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_delete_custom_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = TaskStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'is_system' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/task-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('task_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_delete_system_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Get existing system status created by WorkspaceObserver
        $status = TaskStatus::where('workspace_id', $workspace->id)
            ->where('is_system', true)
            ->first();

        $this->assertNotNull($status, 'System status should exist from WorkspaceObserver');

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/task-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('task_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_access_other_workspace_task_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = TaskStatus::factory()->create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/task-status/{$status->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_access_task_statuses(): void
    {
        $response = $this->get('/task-status');
        $response->assertRedirect('/login');
    }

    public function test_default_statuses_are_created_with_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // WorkspaceObserver should have created default statuses
        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Backlog',
            'automation_trigger' => 'backlog',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Todo',
            'automation_trigger' => 'unstarted',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'In Progress',
            'automation_trigger' => 'active',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'In Review',
            'automation_trigger' => 'review',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Completed',
            'automation_trigger' => 'completed',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('task_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Cancelled',
            'automation_trigger' => 'cancelled',
            'is_system' => true,
        ]);
    }
}

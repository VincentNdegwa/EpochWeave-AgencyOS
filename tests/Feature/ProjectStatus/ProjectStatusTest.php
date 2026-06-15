<?php

namespace Tests\Feature\ProjectStatus;

use App\Models\ProjectStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_project_statuses(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Default statuses are created by WorkspaceObserver
        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Active',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/project-status');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('project-status/index')
                ->has('project_statuses')
            );
    }

    public function test_user_can_create_project_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/project-status', [
                'title' => 'Review',
                'color' => '#a855f7',
                'position' => 5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_statuses', [
            'title' => 'Review',
            'workspace_id' => $workspace->id,
            'color' => '#a855f7',
            'position' => 5,
        ]);
    }

    public function test_user_cannot_create_project_status_without_title(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/project-status', [
                'color' => '#a855f7',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_update_project_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProjectStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/project-status/{$status->id}", [
                'title' => 'Updated Title',
                'color' => '#22c55e',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
            'color' => '#22c55e',
        ]);
    }

    public function test_user_can_delete_custom_project_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProjectStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'is_system' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/project-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('project_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_delete_system_project_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProjectStatus::where('workspace_id', $workspace->id)
            ->where('is_system', true)
            ->first();

        $this->assertNotNull($status, 'System status should exist from WorkspaceObserver');

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/project-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('project_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_access_other_workspace_project_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProjectStatus::factory()->create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/project-status/{$status->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_access_project_statuses(): void
    {
        $response = $this->get('/project-status');
        $response->assertRedirect('/login');
    }

    public function test_default_statuses_are_created_with_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // WorkspaceObserver should have created default statuses
        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Planning',
            'automation_trigger' => 'planning',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Active',
            'automation_trigger' => 'active',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'On Hold',
            'automation_trigger' => 'paused',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Completed',
            'automation_trigger' => 'completed',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('project_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Cancelled',
            'automation_trigger' => 'cancelled',
            'is_system' => true,
        ]);
    }

    public function test_color_must_be_valid_hex(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/project-status', [
                'title' => 'Invalid Color',
                'color' => 'not-a-color',
            ]);

        $response->assertSessionHasErrors('color');
    }
}

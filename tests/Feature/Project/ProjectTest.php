<?php

namespace Tests\Feature\Project;

use App\Models\Account;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsWorkspaceAdmin(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        return [$user, $workspace];
    }

    public function test_user_can_create_project(): void
    {
        [$user, $workspace] = $this->actingAsWorkspaceAdmin();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/projects', [
                'account_id' => $account->id,
                'name' => 'New Project',
                'currency' => 'KES',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'account_id' => $account->id,
            'name' => 'New Project',
        ]);
    }

    public function test_user_can_update_project(): void
    {
        [$user, $workspace] = $this->actingAsWorkspaceAdmin();
        $project = Project::factory()->create(['workspace_id' => $workspace->id]);

        // Get paused status from workspace (default status with automation_trigger = 'paused')
        $pausedStatus = ProjectStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'paused')
            ->first();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/projects/{$project->id}", [
                'name' => 'Updated Project',
                'project_status_id' => $pausedStatus?->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
            'project_status_id' => $pausedStatus?->id,
        ]);
    }

    public function test_user_can_delete_project(): void
    {
        [$user, $workspace] = $this->actingAsWorkspaceAdmin();
        $project = Project::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/projects/{$project->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}

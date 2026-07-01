<?php

namespace Tests\Feature\ProposalStatus;

use App\Models\ProposalStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProposalStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_proposal_statuses(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/setup/proposal-status');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/proposal-status/index')
                ->has('proposal_statuses')
            );
    }

    public function test_user_can_create_proposal_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/setup/proposal-status', [
                'title' => 'Negotiating',
                'color' => '#f59e0b',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposal_statuses', [
            'title' => 'Negotiating',
            'workspace_id' => $workspace->id,
        ]);
    }

    public function test_user_can_update_proposal_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProposalStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/setup/proposal-status/{$status->id}", [
                'title' => 'Updated Title',
                'color' => '#22c55e',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposal_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_delete_custom_proposal_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = ProposalStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'is_system' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/setup/proposal-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('proposal_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_guest_cannot_access_proposal_statuses(): void
    {
        $response = $this->get('/setup/proposal-status');
        $response->assertRedirect('/login');
    }
}

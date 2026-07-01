<?php

namespace Tests\Feature\LeadSource;

use App\Models\LeadSource;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LeadSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_lead_sources(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        LeadSource::factory()->count(3)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/lead-sources');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/lead-source/index')
                ->has('lead_sources', count(LeadSource::getDefaultRecords()) + 3),
            );
    }

    public function test_user_can_create_lead_source(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/lead-sources', [
                'name' => 'Custom Paid Source',
                'color' => '#ef4444',
                'category' => 'paid',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_sources', [
            'workspace_id' => $workspace->id,
            'name' => 'Custom Paid Source',
            'category' => 'paid',
        ]);
    }

    public function test_duplicate_name_in_workspace_is_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        LeadSource::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Custom Paid Source']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/lead-sources', [
                'name' => 'Custom Paid Source',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_user_can_update_lead_source(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $leadSource = LeadSource::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/lead-sources/{$leadSource->id}", [
                'name' => 'Updated Source',
                'category' => 'organic',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_sources', [
            'id' => $leadSource->id,
            'name' => 'Updated Source',
            'category' => 'organic',
        ]);
    }

    public function test_user_cannot_update_lead_source_in_other_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $leadSource = LeadSource::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/lead-sources/{$leadSource->id}", [
                'name' => 'Updated Source',
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_delete_lead_source(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $leadSource = LeadSource::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/lead-sources/{$leadSource->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('lead_sources', [
            'id' => $leadSource->id,
        ]);
    }

    public function test_lead_sources_are_scoped_to_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        LeadSource::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Visible']);
        LeadSource::factory()->create(['workspace_id' => $otherWorkspace->id, 'name' => 'Hidden']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/lead-sources');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/lead-source/index')
                ->has('lead_sources', count(LeadSource::getDefaultRecords()) + 1),
            );
    }
}

<?php

namespace Tests\Feature\Industry;

use App\Models\Industry;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndustryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_industries(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        Industry::factory()->count(3)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/industries');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/industry/index')
                ->has('industries', count(Industry::getDefaultRecords()) + 3),
            );
    }

    public function test_user_can_create_industry(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/industries', [
                'name' => 'Technology',
                'color' => '#3b82f6',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('industries', [
            'workspace_id' => $workspace->id,
            'name' => 'Technology',
            'color' => '#3b82f6',
        ]);
    }

    public function test_duplicate_name_in_workspace_is_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        Industry::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Technology']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/industries', [
                'name' => 'Technology',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_user_can_update_industry(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $industry = Industry::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/industries/{$industry->id}", [
                'name' => 'Updated Name',
                'color' => '#ef4444',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'name' => 'Updated Name',
            'color' => '#ef4444',
        ]);
    }

    public function test_user_cannot_update_industry_in_other_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $industry = Industry::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/industries/{$industry->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_delete_industry(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $industry = Industry::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/industries/{$industry->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('industries', [
            'id' => $industry->id,
        ]);
    }

    public function test_industries_are_scoped_to_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        Industry::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Visible']);
        Industry::factory()->create(['workspace_id' => $otherWorkspace->id, 'name' => 'Hidden']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/industries');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/industry/index')
                ->has('industries', count(Industry::getDefaultRecords()) + 1),
            );
    }
}

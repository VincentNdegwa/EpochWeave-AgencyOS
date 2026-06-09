<?php

namespace Tests\Feature\Workspace;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/workspaces', [
                'name' => 'test-workspace',
                'display_name' => 'Test Workspace',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workspaces', [
            'name' => 'test-workspace',
            'display_name' => 'Test Workspace',
        ]);
        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_switch_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->post("/workspaces/{$workspace->id}/switch");

        $response->assertRedirect();
        $this->assertEquals($workspace->id, session('current_workspace_id'));
    }

    public function test_user_cannot_switch_to_workspace_they_dont_have_access_to(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $response = $this->actingAs($user)
            ->post("/workspaces/{$workspace->id}/switch");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_workspace(): void
    {
        $response = $this->post('/workspaces', [
            'name' => 'test-workspace',
            'display_name' => 'Test Workspace',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_workspace_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->create(['name' => 'test-workspace']);

        $response = $this->actingAs($user)
            ->post('/workspaces', [
                'name' => 'test-workspace',
                'display_name' => 'Test Workspace',
            ]);

        $response->assertSessionHasErrors('name');
    }
}

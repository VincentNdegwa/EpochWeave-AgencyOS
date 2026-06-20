<?php

namespace Tests\Feature\Workspace;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);

        $role = Role::create([
            'name' => 'member',
            'display_name' => 'Member',
        ]);
        $this->user->roles()->attach($role->id, ['workspace_id' => $this->workspace->id]);

        $this->actingAs($this->user);
    }

    public function test_can_list_workspace_members()
    {
        $response = $this->get(route('workspace.members'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('members')
            ->has('roles')
        );
    }

    public function test_can_invite_member_to_workspace()
    {
        $role = Role::create([
            'name' => 'viewer',
            'display_name' => 'Viewer',
        ]);

        $response = $this->post(route('workspace.members.invite'), [
            'email' => 'new@example.com',
            'role_id' => $role->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_can_remove_member_from_workspace()
    {
        $member = User::factory()->create();
        $role = Role::create(['name' => 'viewer']);
        $member->roles()->attach($role->id, ['workspace_id' => $this->workspace->id]);

        $response = $this->delete(route('workspace.members.remove', $member));

        $response->assertRedirect();
        $this->assertFalse($member->roles()->wherePivot('workspace_id', $this->workspace->id)->exists());
    }
}

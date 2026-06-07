<?php

namespace Tests\Feature\Workspace;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspaceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_settings_redirects_to_general(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $this->actingAs($user)
            ->get('/workspace/settings')
            ->assertRedirect('/workspace/settings/general');
    }

    public function test_workspace_settings_general_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $this->actingAs($user)
            ->get('/workspace/settings/general')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('workspace-settings/General'),
            );
    }

    public function test_admin_can_update_workspace_general_settings(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'name' => 'old-name',
            'display_name' => 'Old Name',
            'domain' => 'example.com',
        ]);
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->from(route('workspace-settings.general'))
            ->patch(route('workspace-settings.general.update'), [
                'name' => 'New Workspace Name',
                'description' => 'Updated description',
                'currency' => 'USD',
                'white_label' => true,
                'logo_url' => 'https://example.com/logo.png',
                'primary_color' => '#000000',
                'domain' => 'should-be-ignored.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('workspace-settings.general'));

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'New Workspace Name',
            'display_name' => 'New Workspace Name',
            'description' => 'Updated description',
            'currency' => 'USD',
            'white_label' => 1,
            'logo_url' => 'https://example.com/logo.png',
            'primary_color' => '#000000',
            'domain' => 'example.com',
        ]);
    }

    public function test_non_admin_cannot_update_workspace_general_settings(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'member',
            'display_name' => 'Member',
        ]);

        $user->addRole($role, $workspace);

        $this->actingAs($user)
            ->patch(route('workspace-settings.general.update'), [
                'name' => 'New Workspace Name',
                'white_label' => false,
            ])
            ->assertStatus(403);
    }
}

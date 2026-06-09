<?php

namespace Tests\Feature\Workspace;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
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

    public function test_workspace_settings_proposals_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        WorkspaceSetting::create([
            'workspace_id' => $workspace->id,
            'submodule' => WorkspaceSetting::SUBMODULE_PROPOSALS,
            'settings' => [
                'default_validity_days' => 21,
                'auto_archive' => false,
                'default_deposit_percentage' => 35,
                'payment_due_days' => 10,
                'default_terms' => 'Custom terms',
                'sender_name' => 'Existing User',
                'sender_title' => 'Founder',
                'numbering' => [
                    'format' => '{PREFIX}-{SEQUENCE}',
                    'prefix' => 'CSTM',
                    'delimiter' => '-',
                    'sequence_padding' => 5,
                    'next_sequence_number' => 42,
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('workspace-settings.proposals'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('workspace-settings/Proposals')
                ->where('proposalSettings.default_validity_days', 21)
                ->where('proposalSettings.numbering.format', '{PREFIX}-{SEQUENCE}')
            );
    }

    public function test_admin_can_update_workspace_proposal_settings(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $payload = [
            'default_validity_days' => 30,
            'auto_archive' => true,
            'default_deposit_percentage' => 60,
            'payment_due_days' => 15,
            'default_terms' => 'Net 15 payment terms.',
            'sender_name' => 'Jane Accountable',
            'sender_title' => 'Director of Success',
            'numbering' => [
                'format' => '{PREFIX}/{YEAR}/{SEQUENCE}',
                'prefix' => 'PRO',
                'delimiter' => '/',
                'sequence_padding' => 3,
                'next_sequence_number' => 9,
            ],
        ];

        $this->actingAs($user)
            ->from(route('workspace-settings.proposals'))
            ->patch(route('workspace-settings.proposals.update'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('workspace-settings.proposals'));

        $this->assertDatabaseHas('workspace_settings', [
            'workspace_id' => $workspace->id,
            'submodule' => WorkspaceSetting::SUBMODULE_PROPOSALS,
            'settings->default_validity_days' => 30,
            'settings->auto_archive' => true,
            'settings->default_deposit_percentage' => 60,
            'settings->payment_due_days' => 15,
            'settings->default_terms' => 'Net 15 payment terms.',
            'settings->sender_name' => 'Jane Accountable',
            'settings->sender_title' => 'Director of Success',
            'settings->numbering->format' => '{PREFIX}/{YEAR}/{SEQUENCE}',
            'settings->numbering->prefix' => 'PRO',
            'settings->numbering->delimiter' => '/',
            'settings->numbering->sequence_padding' => 3,
            'settings->numbering->next_sequence_number' => 9,
        ]);
    }

    public function test_non_admin_cannot_update_workspace_proposal_settings(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'member',
            'display_name' => 'Member',
        ]);

        $user->addRole($role, $workspace);

        $this->actingAs($user)
            ->patch(route('workspace-settings.proposals.update'), [
                'default_validity_days' => 30,
                'auto_archive' => false,
                'default_deposit_percentage' => 50,
                'payment_due_days' => 10,
                'numbering' => [
                    'format' => '{PREFIX}{SEQUENCE}',
                    'prefix' => 'PR',
                    'delimiter' => '-',
                    'sequence_padding' => 4,
                    'next_sequence_number' => 4,
                ],
            ])
            ->assertStatus(403);
    }
}

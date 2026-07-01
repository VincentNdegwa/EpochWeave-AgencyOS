<?php

namespace Tests\Feature\SocialProfile;

use App\Models\Account;
use App\Models\Role;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_profile_can_be_created_for_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/social-profiles', [
                'profileable_type' => 'App\Models\Account',
                'profileable_id' => $account->id,
                'platform' => 'linkedin',
                'url' => 'https://linkedin.com/company/test',
                'handle' => 'testcompany',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('social_profiles', [
            'profileable_type' => 'App\Models\Account',
            'profileable_id' => $account->id,
            'platform' => 'linkedin',
            'url' => 'https://linkedin.com/company/test',
        ]);
    }

    public function test_social_profile_can_be_updated(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $profile = SocialProfile::factory()->forAccount($account)->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/social-profiles/{$profile->id}", [
                'platform' => 'twitter',
                'url' => 'https://twitter.com/newhandle',
                'handle' => 'newhandle',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('social_profiles', [
            'id' => $profile->id,
            'platform' => 'twitter',
            'url' => 'https://twitter.com/newhandle',
        ]);
    }

    public function test_social_profile_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $profile = SocialProfile::factory()->forAccount($account)->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/social-profiles/{$profile->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('social_profiles', [
            'id' => $profile->id,
        ]);
    }

    public function test_social_profile_validation_requires_url(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/social-profiles', [
                'profileable_type' => 'App\Models\Account',
                'profileable_id' => 1,
                'platform' => 'linkedin',
            ]);

        $response->assertSessionHasErrors(['url']);
    }
}

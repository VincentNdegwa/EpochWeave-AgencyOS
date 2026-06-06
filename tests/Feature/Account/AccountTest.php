<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = \App\Models\Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts', [
                'company_name' => 'Test Company',
                'website' => 'https://example.com',
                'status' => 'lead',
                'lifetime_value' => 10000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Test Company',
            'workspace_id' => $workspace->id,
        ]);
    }

    public function test_user_can_view_accounts(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = \App\Models\Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/accounts');

        $response->assertStatus(200);
    }

    public function test_user_can_view_single_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = \App\Models\Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get("/accounts/{$account->id}");

        $response->assertStatus(200);
    }

    public function test_user_can_update_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = \App\Models\Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/accounts/{$account->id}", [
                'company_name' => 'Updated Company',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'company_name' => 'Updated Company',
        ]);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = \App\Models\Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/accounts/{$account->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_guest_cannot_access_accounts(): void
    {
        $response = $this->get('/accounts');
        $response->assertRedirect('/login');
    }
}

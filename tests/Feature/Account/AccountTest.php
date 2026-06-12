<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts', [
                'company_name' => 'Test Company',
                'website' => 'https://example.com',
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
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        Account::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => 'lead',
            'created_at' => now()->subDays(2),
        ]);

        Account::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => 'client',
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/accounts');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('account/index')
                ->has('stats.total.value')
                ->has('stats.total.change.value')
                ->has('stats.total.change.label')
                ->has('stats.lead.value')
                ->has('stats.opportunity.value')
                ->has('stats.client.value')
                ->has('stats.archived.value'),
            );

        $responseWithDates = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/accounts?date_from='.now()->subDays(2)->toDateString().'&date_to='.now()->toDateString());

        $responseWithDates->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('account/index')
                ->has('stats.total.value')
                ->has('stats.total.change.value')
                ->has('stats.total.change.label'),
            );
    }

    public function test_user_can_view_single_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
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
        $role = Role::create(['name' => 'admin']);
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
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/accounts/{$account->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_guest_cannot_access_accounts(): void
    {
        $response = $this->get('/accounts');
        $response->assertRedirect('/login');
    }
}

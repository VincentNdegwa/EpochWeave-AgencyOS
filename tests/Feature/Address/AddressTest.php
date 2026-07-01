<?php

namespace Tests\Feature\Address;

use App\Models\Account;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_address_can_be_created_for_account(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/addresses', [
                'addressable_type' => 'App\Models\Account',
                'addressable_id' => $account->id,
                'type' => 'billing',
                'street_1' => '123 Main St',
                'city' => 'Nairobi',
                'country' => 'Kenya',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'addressable_type' => 'App\Models\Account',
            'addressable_id' => $account->id,
            'type' => 'billing',
            'street_1' => '123 Main St',
        ]);
    }

    public function test_address_can_be_updated(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $address = Address::factory()->forAccount($account)->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/addresses/{$address->id}", [
                'type' => 'shipping',
                'street_1' => '456 Updated St',
                'city' => 'Mombasa',
                'country' => 'Kenya',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'type' => 'shipping',
            'street_1' => '456 Updated St',
        ]);
    }

    public function test_address_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $address = Address::factory()->forAccount($account)->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/addresses/{$address->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_primary_address_unsets_others_of_same_type(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $existing = Address::factory()->forAccount($account)->billing()->primary()->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/addresses', [
                'addressable_type' => 'App\Models\Account',
                'addressable_id' => $account->id,
                'type' => 'billing',
                'street_1' => '789 New St',
                'city' => 'Kisumu',
                'country' => 'Kenya',
                'is_primary' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $existing->id,
            'is_primary' => false,
        ]);
    }

    public function test_address_validation_requires_required_fields(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/addresses', [
                'addressable_type' => 'App\Models\Account',
                'addressable_id' => 1,
            ]);

        $response->assertSessionHasErrors(['type', 'street_1', 'city', 'country']);
    }

    public function test_set_primary_endpoint_works(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $address = Address::factory()->forAccount($account)->billing()->create();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post("/addresses/{$address->id}/primary");

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_primary' => true,
        ]);
    }
}

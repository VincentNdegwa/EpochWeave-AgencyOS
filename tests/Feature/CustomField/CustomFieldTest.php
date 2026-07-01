<?php

namespace Tests\Feature\CustomField;

use App\Models\Account;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\CustomFieldService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_custom_field_groups(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        CustomFieldGroup::factory()->count(2)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/setup/custom-fields');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/custom-field/index')
                ->has('groups', 2),
            );
    }

    public function test_user_can_create_field_group(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/custom-field-groups', [
                'name' => 'Company Details',
                'applies_to' => 'App\Models\Account',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_field_groups', [
            'workspace_id' => $workspace->id,
            'name' => 'Company Details',
            'applies_to' => 'App\Models\Account',
        ]);
    }

    public function test_user_can_update_field_group(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $group = CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/custom-field-groups/{$group->id}", [
                'name' => 'Updated Name',
                'applies_to' => 'App\Models\Account',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_field_groups', [
            'id' => $group->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_delete_field_group(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $group = CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/custom-field-groups/{$group->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('custom_field_groups', [
            'id' => $group->id,
        ]);
    }

    public function test_user_can_create_field_definition(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $group = CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/custom-field-definitions', [
                'custom_field_group_id' => $group->id,
                'label' => 'VAT Number',
                'field_type' => 'text',
                'is_required' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_field_definitions', [
            'custom_field_group_id' => $group->id,
            'label' => 'VAT Number',
            'field_type' => 'text',
            'is_required' => true,
        ]);
    }

    public function test_user_can_update_field_definition(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $definition = CustomFieldDefinition::factory()->create([
            'custom_field_group_id' => CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id])->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/custom-field-definitions/{$definition->id}", [
                'label' => 'Updated Label',
                'field_type' => 'number',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_field_definitions', [
            'id' => $definition->id,
            'label' => 'Updated Label',
            'field_type' => 'number',
        ]);
    }

    public function test_user_can_delete_field_definition(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $definition = CustomFieldDefinition::factory()->create([
            'custom_field_group_id' => CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id])->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/custom-field-definitions/{$definition->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('custom_field_definitions', [
            'id' => $definition->id,
        ]);
    }

    public function test_field_values_can_be_saved(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $definition = CustomFieldDefinition::factory()->create([
            'field_type' => 'text',
            'custom_field_group_id' => CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id])->id,
        ]);

        $service = app(CustomFieldService::class);
        $service->saveValues($account, [$definition->id => 'Test Value']);

        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_definition_id' => $definition->id,
            'valueable_type' => 'App\Models\Account',
            'valueable_id' => $account->id,
            'value_text' => 'Test Value',
        ]);
    }

    public function test_groups_are_scoped_to_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        CustomFieldGroup::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Visible']);
        CustomFieldGroup::factory()->create(['workspace_id' => $otherWorkspace->id, 'name' => 'Hidden']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/setup/custom-fields');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/custom-field/index')
                ->has('groups', 1)
                ->where('groups.0.name', 'Visible'),
            );
    }
}

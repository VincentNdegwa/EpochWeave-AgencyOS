<?php

namespace Tests\Feature\CompanySize;

use App\Models\CompanySize;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanySizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_company_sizes(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        CompanySize::factory()->count(3)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/company-sizes');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/company-size/index')
                ->has('company_sizes', count(CompanySize::getDefaultRecords()) + 3),
            );
    }

    public function test_user_can_create_company_size(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/company-sizes', [
                'label' => 'Custom Size',
                'min_employees' => 11,
                'max_employees' => 50,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('company_sizes', [
            'workspace_id' => $workspace->id,
            'label' => 'Custom Size',
            'min_employees' => 11,
            'max_employees' => 50,
        ]);
    }

    public function test_duplicate_label_in_workspace_is_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        CompanySize::factory()->create(['workspace_id' => $workspace->id, 'label' => 'Custom Size']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/company-sizes', [
                'label' => 'Custom Size',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_user_can_update_company_size(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $companySize = CompanySize::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/company-sizes/{$companySize->id}", [
                'label' => 'Custom Updated Size',
                'min_employees' => 51,
                'max_employees' => 200,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('company_sizes', [
            'id' => $companySize->id,
            'label' => 'Custom Updated Size',
        ]);
    }

    public function test_user_can_delete_company_size(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);
        $companySize = CompanySize::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/company-sizes/{$companySize->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('company_sizes', [
            'id' => $companySize->id,
        ]);
    }
}

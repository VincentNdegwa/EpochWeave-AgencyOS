<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkspaceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkspaceService();
    }

    public function test_create_workspace_creates_workspace_and_assigns_admin_role(): void
    {
        $user = User::factory()->create();

        $workspace = $this->service->createWorkspace($user, [
            'name' => 'test-workspace',
            'display_name' => 'Test Workspace',
            'description' => 'Test description',
        ]);

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'test-workspace',
            'display_name' => 'Test Workspace',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
        ]);

        $this->assertTrue($user->hasRole('admin', $workspace));
    }

    public function test_create_workspace_uses_transaction_on_failure(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->service->createWorkspace($user, [
            'name' => null, // This should fail validation
        ]);

        $this->assertDatabaseMissing('workspaces', [
            'name' => null,
        ]);
    }

    public function test_switch_workspace_sets_session(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);
        $user->addRole($role, $workspace);

        $this->service->switchWorkspace($user, $workspace);

        $this->assertEquals($workspace->id, session('current_workspace_id'));
    }

    public function test_switch_workspace_fails_without_access(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->service->switchWorkspace($user, $workspace);
    }

    public function test_create_workspace_with_optional_fields(): void
    {
        $user = User::factory()->create();

        $workspace = $this->service->createWorkspace($user, [
            'name' => 'test-workspace',
            'display_name' => 'Test Workspace',
            'currency' => 'USD',
            'white_label' => true,
            'domain' => 'example.com',
            'logo_url' => 'https://example.com/logo.png',
            'primary_color' => '#ffffff',
        ]);

        $this->assertEquals('USD', $workspace->currency);
        $this->assertTrue($workspace->white_label);
        $this->assertEquals('example.com', $workspace->domain);
        $this->assertEquals('https://example.com/logo.png', $workspace->logo_url);
        $this->assertEquals('#ffffff', $workspace->primary_color);
    }
}

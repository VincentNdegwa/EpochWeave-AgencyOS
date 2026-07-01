<?php

namespace Tests\Feature\InvoiceStatus;

use App\Models\InvoiceStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_invoice_statuses(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Default statuses are created by WorkspaceObserver
        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Paid',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/setup/invoice-status');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('setup/invoice-status/index')
                ->has('invoice_statuses')
            );
    }

    public function test_user_can_create_invoice_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/setup/invoice-status', [
                'title' => 'Partially Paid',
                'color' => '#f59e0b',
                'position' => 5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_statuses', [
            'title' => 'Partially Paid',
            'workspace_id' => $workspace->id,
            'color' => '#f59e0b',
            'position' => 5,
        ]);
    }

    public function test_user_cannot_create_invoice_status_without_title(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/setup/invoice-status', [
                'color' => '#f59e0b',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_update_invoice_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = InvoiceStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/setup/invoice-status/{$status->id}", [
                'title' => 'Updated Title',
                'color' => '#22c55e',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
            'color' => '#22c55e',
        ]);
    }

    public function test_user_can_delete_custom_invoice_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = InvoiceStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'is_system' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/setup/invoice-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('invoice_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_delete_system_invoice_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // Get existing system status created by WorkspaceObserver
        $status = InvoiceStatus::where('workspace_id', $workspace->id)
            ->where('is_system', true)
            ->first();

        $this->assertNotNull($status, 'System status should exist from WorkspaceObserver');

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete("/setup/invoice-status/{$status->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $status->id,
        ]);
    }

    public function test_user_cannot_access_other_workspace_invoice_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $status = InvoiceStatus::factory()->create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put("/setup/invoice-status/{$status->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_access_invoice_statuses(): void
    {
        $response = $this->get('/setup/invoice-status');
        $response->assertRedirect('/login');
    }

    public function test_default_statuses_are_created_with_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        // WorkspaceObserver should have created default statuses
        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Draft',
            'automation_trigger' => 'draft',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Sent',
            'automation_trigger' => 'sent',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Overdue',
            'automation_trigger' => 'overdue',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Paid',
            'automation_trigger' => 'paid',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('invoice_statuses', [
            'workspace_id' => $workspace->id,
            'title' => 'Voided',
            'automation_trigger' => 'voided',
            'is_system' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $user->workspaces()->attach($workspace->id, ['role' => 'owner']);
        $this->actingAs($user);

        $response = $this->withHeaders(['X-Workspace-Id' => $workspace->id])
            ->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_returns_stats_props()
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $user->workspaces()->attach($workspace->id, ['role' => 'owner']);

        $sentStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'sent')
            ->firstOrFail();
        Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'proposal_status_id' => $sentStatus->id,
            'grand_total' => 1000,
        ]);

        $draftInvoiceStatus = InvoiceStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->firstOrFail();
        Invoice::factory()->create([
            'workspace_id' => $workspace->id,
            'invoice_status_id' => $draftInvoiceStatus->id,
            'grand_total' => 500,
        ]);

        $taskStatus = TaskStatus::factory()->create([
            'workspace_id' => $workspace->id,
            'is_closed' => false,
        ]);
        $project = Project::factory()->create(['workspace_id' => $workspace->id]);
        Task::factory()->create([
            'workspace_id' => $workspace->id,
            'task_status_id' => $taskStatus->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);
        $response = $this->withHeaders(['X-Workspace-Id' => $workspace->id])
            ->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('stats')
            ->has('recentActivity')
            ->has('upcomingDeadlines')
            ->has('revenueChart')
        );
    }
}

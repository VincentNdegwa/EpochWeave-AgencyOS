<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);
        $this->actingAs($this->user);
    }

    public function test_can_view_reports_page()
    {
        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('year')
            ->has('revenueByMonth')
            ->has('proposalStatusCounts')
            ->has('topClients')
            ->has('hoursByMonth')
        );
    }

    public function test_can_filter_reports_by_year()
    {
        $response = $this->get(route('reports.index', ['year' => 2024]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('year', 2024)
        );
    }
}

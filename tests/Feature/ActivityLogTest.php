<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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

    public function test_can_list_activities()
    {
        Activity::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'type' => 'invoice.created',
            'subject_type' => 'App\Models\Invoice',
            'subject_id' => 1,
            'description' => 'Created invoice INV-001',
        ]);

        $response = $this->get(route('activities.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('activities.data', 1)
        );
    }
}

<?php

namespace Tests\Feature\Proposal;

use App\Models\Account;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProposalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_is_recorded_as_creator(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.store'), [
                'account_id' => $account->id,
                'title' => 'Website redesign proposal',
                'currency' => 'USD',
                'valid_until' => now()->addWeek()->toDateString(),
                'blocks' => $this->validBlocksPayload(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('proposals', [
            'account_id' => $account->id,
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
            'title' => 'Website redesign proposal',
        ]);
    }

    public function test_created_by_cannot_be_overridden_via_payload(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create([
            'workspace_id' => $workspace->id,
        ]);
        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.store'), [
                'account_id' => $account->id,
                'title' => 'Brand strategy retainer',
                'currency' => 'USD',
                'valid_until' => now()->addDays(10)->toDateString(),
                'blocks' => $this->validBlocksPayload(),
                'created_by' => $otherUser->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'title' => 'Brand strategy retainer',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseMissing('proposals', [
            'title' => 'Brand strategy retainer',
            'created_by' => $otherUser->id,
        ]);
    }

    public function test_proposal_numbering_uses_workspace_settings_and_increments_sequence(): void
    {
        Carbon::setTestNow('2026-06-09 00:00:00');

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        WorkspaceSetting::create([
            'workspace_id' => $workspace->id,
            'submodule' => WorkspaceSetting::SUBMODULE_PROPOSALS,
            'settings' => [
                'numbering' => [
                    'format' => '{PREFIX}{YEAR}{DELIMITER}{SEQUENCE}',
                    'prefix' => 'ERP',
                    'delimiter' => '#',
                    'sequence_padding' => 5,
                    'next_sequence_number' => 42,
                ],
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.store'), [
                'account_id' => $account->id,
                'title' => 'Custom numbering checks',
                'currency' => 'USD',
                'valid_until' => now()->addDays(5)->toDateString(),
                'blocks' => $this->validBlocksPayload(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'title' => 'Custom numbering checks',
            'proposal_number' => 'ERP2026#00042',
        ]);

        $updatedSettings = WorkspaceSetting::where('workspace_id', $workspace->id)
            ->where('submodule', WorkspaceSetting::SUBMODULE_PROPOSALS)
            ->first();

        $this->assertEquals(43, $updatedSettings->settings['numbering']['next_sequence_number']);
    }

    private function validBlocksPayload(): array
    {
        return [[
            'id' => 'block-1',
            'type' => 'cover',
            'sort_order' => 0,
            'is_locked' => false,
            'data' => ['title' => 'Cover'],
            'meta' => [
                'padding_top' => 'md',
                'padding_bottom' => 'md',
                'background_color' => '#ffffff',
                'border_top' => false,
                'border_bottom' => false,
                'is_hidden' => false,
                'notes' => null,
            ],
        ]];
    }
}

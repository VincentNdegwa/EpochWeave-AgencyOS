<?php

namespace Tests\Feature\Proposal;

use App\Models\Account;
use App\Models\Proposal;
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

    public function test_blocks_payload_is_persisted(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $blocks = [
            [
                'id' => 'block-1',
                'type' => 'rich_text',
                'sort_order' => 0,
                'is_locked' => false,
                'data' => ['title' => 'Intro', 'content' => '<p>Hello world</p>', 'show_title' => true, 'full_width' => true],
                'meta' => [
                    'padding_top' => 'lg',
                    'padding_bottom' => 'lg',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],
            [
                'id' => 'block-2',
                'type' => 'pricing_table',
                'sort_order' => 1,
                'is_locked' => false,
                'data' => [
                    'title' => 'Pricing',
                    'items' => [],
                    'discount' => null,
                    'show_quantity_column' => true,
                    'show_unit_column' => true,
                    'show_subtotal_per_line' => true,
                    'show_subtotal_row' => true,
                    'show_tax_row' => true,
                    'tax_label' => 'VAT',
                    'tax_rate' => 0,
                    'show_total_row' => true,
                    'currency' => 'USD',
                    'footer_note' => null,
                ],
                'meta' => [
                    'padding_top' => 'md',
                    'padding_bottom' => 'md',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => 'internal note',
                ],
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.store'), [
                'account_id' => $account->id,
                'title' => 'Proposal with blocks',
                'currency' => 'USD',
                'valid_until' => now()->addWeek()->toDateString(),
                'blocks' => $blocks,
            ]);

        $response->assertRedirect();

        $proposal = Proposal::where('title', 'Proposal with blocks')->first();

        $this->assertNotNull($proposal);
        $this->assertCount(2, $proposal->content);
        $this->assertSame($blocks[0]['id'], $proposal->content[0]['id']);
        $this->assertSame('rich_text', $proposal->content[0]['type']);
        $this->assertSame('internal note', $proposal->content[1]['meta']['notes']);
    }

    public function test_blocks_are_required_by_form_request(): void
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
                'title' => 'Missing blocks',
                'currency' => 'USD',
                'valid_until' => now()->addWeek()->toDateString(),
            ]);

        $response->assertSessionHasErrors('blocks');
        $this->assertDatabaseMissing('proposals', [
            'title' => 'Missing blocks',
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

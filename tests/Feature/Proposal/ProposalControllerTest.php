<?php

namespace Tests\Feature\Proposal;

use App\Enums\AccountStatus;
use App\Models\Account;
use App\Models\AccountContact;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStatus;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

    public function test_blocks_are_optional_when_creating_proposal(): void
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
                'title' => 'No blocks yet',
                'currency' => 'USD',
                'valid_until' => now()->addWeek()->toDateString(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('proposals', [
            'title' => 'No blocks yet',
            'workspace_id' => $workspace->id,
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

    public function test_duplicate_proposal_creates_draft_copy_with_items(): void
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
                    'format' => '{PREFIX}{DELIMITER}{SEQUENCE}',
                    'prefix' => 'PROP',
                    'delimiter' => '-',
                    'sequence_padding' => 4,
                    'next_sequence_number' => 10,
                ],
            ],
        ]);

        $draftStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->first();

        $sentStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'sent')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'proposal_status_id' => $sentStatus->id,
            'proposal_number' => 'PROP-ORIG-001',
            'title' => 'Original Proposal',
            'currency' => 'USD',
            'sent_at' => now()->subDay(),
            'view_count' => 5,
        ]);

        ProposalItem::factory()->create([
            'proposal_id' => $proposal->id,
            'item_name' => 'Design Service',
            'quantity' => 2,
            'unit_price' => 500,
            'subtotal' => 1000,
            'total' => 1000,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.duplicate', $proposal));

        $response->assertRedirect();

        $original = Proposal::where('proposal_number', 'PROP-ORIG-001')->first();
        $this->assertNotNull($original);

        $duplicate = Proposal::where('title', 'Original Proposal')
            ->where('id', '!=', $original->id)
            ->first();

        $this->assertNotNull($duplicate);
        $this->assertEquals($draftStatus->id, $duplicate->proposal_status_id);
        $this->assertNotEquals($original->proposal_number, $duplicate->proposal_number);
        $this->assertNull($duplicate->sent_at);
        $this->assertNull($duplicate->viewed_at);
        $this->assertNull($duplicate->accepted_at);
        $this->assertNull($duplicate->signed_at);
        $this->assertNull($duplicate->expired_at);
        $this->assertEquals(0, $duplicate->view_count);
        $this->assertNull($duplicate->project_id);

        $this->assertCount(1, $duplicate->items);
        $this->assertEquals('Design Service', $duplicate->items->first()->item_name);
        $this->assertEquals(1000, $duplicate->items->first()->subtotal);
    }

    public function test_duplicate_proposal_increments_sequence_number(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        WorkspaceSetting::create([
            'workspace_id' => $workspace->id,
            'submodule' => WorkspaceSetting::SUBMODULE_PROPOSALS,
            'settings' => [
                'numbering' => [
                    'format' => '{PREFIX}{DELIMITER}{SEQUENCE}',
                    'prefix' => 'PROP',
                    'delimiter' => '-',
                    'sequence_padding' => 4,
                    'next_sequence_number' => 7,
                ],
            ],
        ]);

        $sentStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'sent')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'proposal_status_id' => $sentStatus->id,
            'title' => 'Sequence Test',
        ]);

        $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.duplicate', $proposal));

        $updatedSettings = WorkspaceSetting::where('workspace_id', $workspace->id)
            ->where('submodule', WorkspaceSetting::SUBMODULE_PROPOSALS)
            ->first();

        $this->assertEquals(8, $updatedSettings->settings['numbering']['next_sequence_number']);
    }

    public function test_move_proposal_reverts_sent_to_draft(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $draftStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->first();

        $sentStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'sent')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'proposal_status_id' => $sentStatus->id,
            'sent_at' => now()->subDay(),
            'title' => 'Sent Proposal',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->patch(route('proposal.move', $proposal), [
                'target_status_id' => $draftStatus->id,
            ]);

        $response->assertRedirect();

        $proposal->refresh();
        $this->assertEquals($draftStatus->id, $proposal->proposal_status_id);
    }

    public function test_move_proposal_blocks_expired_target(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $expiredStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'expired')
            ->first();

        $sentStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'sent')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'proposal_status_id' => $sentStatus->id,
            'sent_at' => now()->subDay(),
            'title' => 'Sent Proposal',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->patch(route('proposal.move', $proposal), [
                'target_status_id' => $expiredStatus->id,
            ]);

        $response->assertRedirect();

        $proposal->refresh();
        $this->assertEquals($sentStatus->id, $proposal->proposal_status_id);
    }

    public function test_sending_proposal_promotes_lead_account_to_opportunity(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => AccountStatus::Lead->value,
        ]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
        ]);

        $draftStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'proposal_status_id' => $draftStatus->id,
            'token' => Str::uuid(),
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.send', $proposal));

        $account->refresh();
        $this->assertEquals(AccountStatus::Opportunity, $account->status);
    }

    public function test_sending_proposal_does_not_change_opportunity_account_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => AccountStatus::Opportunity->value,
        ]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
        ]);

        $draftStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'proposal_status_id' => $draftStatus->id,
            'token' => Str::uuid(),
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.send', $proposal));

        $account->refresh();
        $this->assertEquals(AccountStatus::Opportunity, $account->status);
    }

    public function test_sending_proposal_does_not_change_client_account_status(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => AccountStatus::Client->value,
        ]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
        ]);

        $draftStatus = ProposalStatus::where('workspace_id', $workspace->id)
            ->where('automation_trigger', 'draft')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'proposal_status_id' => $draftStatus->id,
            'token' => Str::uuid(),
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('proposals.send', $proposal));

        $account->refresh();
        $this->assertEquals(AccountStatus::Client, $account->status);
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

<?php

namespace Tests\Feature\Portal;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_can_be_created(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'invitation_token' => 'test-token-123',
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertDatabaseHas('portal_invitations', [
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'invitation_token' => 'test-token-123',
        ]);
    }

    public function test_invitation_is_valid_when_not_used_and_not_expired(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertTrue($invitation->isValid());
    }

    public function test_invitation_is_invalid_when_used(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => true,
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertFalse($invitation->isValid());
    }

    public function test_invitation_is_invalid_when_expired(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->subDays(1),
        ]);

        $this->assertFalse($invitation->isValid());
    }

    public function test_invitation_belongs_to_account(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $this->assertEquals($account->id, $invitation->account->id);
    }

    public function test_invitation_belongs_to_account_contact(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $this->assertEquals($contact->id, $invitation->accountContact->id);
    }

    public function test_invitation_token_is_unique(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'invitation_token' => 'unique-token-123',
        ]);

        $this->expectException(QueryException::class);

        PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'invitation_token' => 'unique-token-123',
        ]);
    }

    public function test_invitation_casts_is_used_to_boolean(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => 1,
        ]);

        $this->assertIsBool($invitation->is_used);
        $this->assertTrue($invitation->is_used);
    }

    public function test_invitation_casts_expires_at_to_datetime(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'expires_at' => '2026-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(CarbonImmutable::class, $invitation->expires_at);
    }
}

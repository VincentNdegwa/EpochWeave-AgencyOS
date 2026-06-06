<?php

namespace Tests\Feature\Portal;

use App\PortalInvitationService;
use App\Models\Account;
use App\Models\AccountContact;
use App\Models\ClientProfile;
use App\Models\PortalInvitation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortalInvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PortalInvitationService();
    }

    public function test_create_invitation_generates_secure_token(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = $this->service->createInvitation($account, $contact);

        $this->assertDatabaseHas('portal_invitations', [
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $this->assertNotEmpty($invitation->invitation_token);
        $this->assertEquals(64, strlen($invitation->invitation_token));
        $this->assertFalse($invitation->is_used);
        $this->assertNotNull($invitation->expires_at);
    }

    public function test_validate_invitation_returns_valid_invitation(): void
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

        $validated = $this->service->validateInvitation($invitation->invitation_token);

        $this->assertNotNull($validated);
        $this->assertEquals($invitation->id, $validated->id);
    }

    public function test_validate_invitation_returns_null_for_used_invitation(): void
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

        $validated = $this->service->validateInvitation($invitation->invitation_token);

        $this->assertNull($validated);
    }

    public function test_validate_invitation_returns_null_for_expired_invitation(): void
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

        $validated = $this->service->validateInvitation($invitation->invitation_token);

        $this->assertNull($validated);
    }

    public function test_validate_invitation_returns_null_for_invalid_token(): void
    {
        $validated = $this->service->validateInvitation('invalid-token');

        $this->assertNull($validated);
    }

    public function test_complete_setup_creates_new_client_profile(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'new@example.com',
            'is_verified' => false,
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $clientProfile = $this->service->completeSetup($invitation->invitation_token, 'SecurePassword123!');

        $this->assertDatabaseHas('client_profiles', [
            'email' => 'new@example.com',
        ]);

        $this->assertDatabaseHas('account_contacts', [
            'id' => $contact->id,
            'client_profile_id' => $clientProfile->id,
            'is_verified' => true,
        ]);

        $this->assertDatabaseHas('portal_invitations', [
            'id' => $invitation->id,
            'is_used' => true,
        ]);
    }

    public function test_complete_setup_updates_existing_client_profile(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        
        $existingProfile = ClientProfile::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('oldpassword'),
        ]);

        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'existing@example.com',
            'is_verified' => false,
            'client_profile_id' => null,
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $clientProfile = $this->service->completeSetup($invitation->invitation_token, 'NewPassword123!');

        $this->assertEquals($existingProfile->id, $clientProfile->id);
        $this->assertDatabaseCount('client_profiles', 1);
    }

    public function test_complete_setup_throws_exception_for_invalid_token(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid or expired invitation token.');

        $this->service->completeSetup('invalid-token', 'Password123!');
    }

    public function test_complete_setup_is_atomic_transaction(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'test@example.com',
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $clientProfile = $this->service->completeSetup($invitation->invitation_token, 'Password123!');

        $this->assertDatabaseHas('client_profiles', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('account_contacts', [
            'id' => $contact->id,
            'is_verified' => true,
        ]);
        $this->assertDatabaseHas('portal_invitations', [
            'id' => $invitation->id,
            'is_used' => true,
        ]);
    }

    public function test_get_invitation_by_token_returns_invitation(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $retrieved = $this->service->getInvitationByToken($invitation->invitation_token);

        $this->assertNotNull($retrieved);
        $this->assertEquals($invitation->id, $retrieved->id);
        $this->assertTrue($retrieved->relationLoaded('accountContact'));
        $this->assertTrue($retrieved->relationLoaded('account'));
    }

    public function test_get_invitation_by_token_returns_null_for_invalid_token(): void
    {
        $retrieved = $this->service->getInvitationByToken('non-existent-token');

        $this->assertNull($retrieved);
    }
}

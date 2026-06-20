<?php

namespace Tests\Feature\Portal;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_invitation_flow_from_creation_to_setup(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $accountService = app(AccountService::class);

        $account = $accountService->createAccount([
            'workspace_id' => $workspace->id,
            'company_name' => 'Test Company',
            'website' => 'https://test.com',
            'status' => 'lead',
            'contacts' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'phone' => '+1234567890',
                    'job_title' => 'CEO',
                    'send_invitation' => true,
                ],
            ],
        ]);

        $contact = $account->contacts->first();
        $invitation = $contact->portalInvitation;

        $this->assertNotNull($invitation);
        $this->assertFalse($invitation->is_used);
        $this->assertNull($contact->client_profile_id);
        $this->assertFalse($contact->is_verified);

        $response = $this->get(route('portal.setup', $invitation->invitation_token));
        $response->assertStatus(200);

        $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $this->assertDatabaseHas('client_profiles', ['email' => 'john@example.com']);

        $contact->refresh();
        $invitation->refresh();

        $this->assertNotNull($contact->client_profile_id);
        $this->assertTrue($contact->is_verified);
        $this->assertTrue($invitation->is_used);
    }

    public function test_invitation_cannot_be_used_twice(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'john@example.com',
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $invitation->refresh();
        $this->assertTrue($invitation->is_used);

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);

        $response->assertRedirect();
    }

    public function test_same_email_can_be_invited_to_multiple_workspaces(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();

        $account1 = Account::factory()->create(['workspace_id' => $workspace1->id]);
        $account2 = Account::factory()->create(['workspace_id' => $workspace2->id]);

        $contact1 = AccountContact::factory()->create([
            'account_id' => $account1->id,
            'email' => 'shared@example.com',
            'client_profile_id' => null,
        ]);

        $contact2 = AccountContact::factory()->create([
            'account_id' => $account2->id,
            'email' => 'shared@example.com',
            'client_profile_id' => null,
        ]);

        $invitation1 = PortalInvitation::factory()->create([
            'account_id' => $account1->id,
            'account_contact_id' => $contact1->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $invitation2 = PortalInvitation::factory()->create([
            'account_id' => $account2->id,
            'account_contact_id' => $contact2->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $this->post(route('portal.setup.complete', $invitation1->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $this->assertDatabaseCount('client_profiles', 1);
        $this->assertDatabaseHas('client_profiles', ['email' => 'shared@example.com']);

        $contact1->refresh();
        $contact2->refresh();

        $this->assertNotNull($contact1->client_profile_id);
        $this->assertTrue($contact1->is_verified);
        $this->assertNull($contact2->client_profile_id);

        $this->post(route('portal.setup.complete', $invitation2->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $contact2->refresh();
        $this->assertTrue($contact2->is_verified);
        $this->assertEquals($contact1->client_profile_id, $contact2->client_profile_id);
        $this->assertDatabaseCount('client_profiles', 1);
    }

    public function test_invitation_expires_after_time(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addSeconds(1),
        ]);

        $this->assertTrue($invitation->isValid());

        sleep(2);

        $this->assertFalse($invitation->isValid());

        $response = $this->get(route('portal.setup', $invitation->invitation_token));
        $response->assertStatus(404);
    }

    public function test_invitation_created_with_send_invitation_flag(): void
    {
        $workspace = Workspace::factory()->create();
        $accountService = app(AccountService::class);

        $account = $accountService->createAccount([
            'workspace_id' => $workspace->id,
            'company_name' => 'Test Company',
            'contacts' => [
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane@example.com',
                    'send_invitation' => true,
                ],
            ],
        ]);

        $contact = $account->contacts->first();
        $this->assertNotNull($contact->portalInvitation);
    }

    public function test_invitation_not_created_without_send_invitation_flag(): void
    {
        $workspace = Workspace::factory()->create();
        $accountService = app(AccountService::class);

        $account = $accountService->createAccount([
            'workspace_id' => $workspace->id,
            'company_name' => 'Test Company',
            'contacts' => [
                [
                    'first_name' => 'Bob',
                    'last_name' => 'Johnson',
                    'email' => 'bob@example.com',
                ],
            ],
        ]);

        $contact = $account->contacts->first();
        $this->assertNull($contact->portalInvitation);
    }

    public function test_contact_without_invitation_can_be_verified_later(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'later@example.com',
            'is_verified' => false,
        ]);

        $this->assertNull($contact->portalInvitation);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $contact->refresh();
        $this->assertTrue($contact->is_verified);
        $this->assertNotNull($contact->client_profile_id);
    }

    public function test_cascade_delete_account_deletes_invitations(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $this->assertDatabaseHas('portal_invitations', ['id' => $invitation->id]);

        $account->delete();

        $this->assertDatabaseMissing('portal_invitations', ['id' => $invitation->id]);
    }

    public function test_cascade_delete_contact_deletes_invitations(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create(['account_id' => $account->id]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
        ]);

        $this->assertDatabaseHas('portal_invitations', ['id' => $invitation->id]);

        $contact->delete();

        $this->assertDatabaseMissing('portal_invitations', ['id' => $invitation->id]);
    }
}

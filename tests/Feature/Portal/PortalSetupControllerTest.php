<?php

namespace Tests\Feature\Portal;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSetupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_renders_setup_page_with_valid_token(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'client@example.com',
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get(route('portal.setup', $invitation->invitation_token));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('portal/setup')
                ->has('token')
                ->has('email')
                ->has('company_name');
        });
    }

    public function test_show_returns_404_for_used_invitation(): void
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

        $response = $this->get(route('portal.setup', $invitation->invitation_token));

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_expired_invitation(): void
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

        $response = $this->get(route('portal.setup', $invitation->invitation_token));

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('portal.setup', 'invalid-token'));

        $response->assertStatus(404);
    }

    public function test_complete_redirects_to_login_with_success_message(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'client@example.com',
        ]);

        $invitation = PortalInvitation::factory()->create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_complete_creates_client_profile_and_verifies_contact(): void
    {
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'email' => 'client@example.com',
            'is_verified' => false,
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

        $this->assertDatabaseHas('client_profiles', ['email' => 'client@example.com']);
        $this->assertDatabaseHas('account_contacts', [
            'id' => $contact->id,
            'is_verified' => true,
        ]);
        $this->assertDatabaseHas('portal_invitations', [
            'id' => $invitation->id,
            'is_used' => true,
        ]);
    }

    public function test_complete_requires_password(): void
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

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_complete_requires_password_confirmation(): void
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

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_complete_requires_minimum_password_length(): void
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

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_complete_returns_error_for_invalid_token(): void
    {
        $response = $this->post(route('portal.setup.complete', 'invalid-token'), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect();
    }

    public function test_complete_returns_error_for_used_invitation(): void
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

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect();
    }

    public function test_complete_returns_error_for_expired_invitation(): void
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

        $response = $this->post(route('portal.setup.complete', $invitation->invitation_token), [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect();
    }
}

<?php

namespace Tests\Feature\Portal;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\ClientProfile;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_login()
    {
        $client = ClientProfile::factory()->create([
            'email' => 'client@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email' => 'client@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated('client');
    }

    public function test_client_cannot_login_with_invalid_password()
    {
        $client = ClientProfile::factory()->create([
            'email' => 'client@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email' => 'client@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('client');
    }

    public function test_authenticated_client_can_access_dashboard()
    {
        $workspace = Workspace::factory()->create();
        $client = ClientProfile::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $contact = AccountContact::factory()->create([
            'account_id' => $account->id,
            'client_profile_id' => $client->id,
        ]);

        $status = ProposalStatus::factory()->create(['workspace_id' => $workspace->id]);
        Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_contact_id' => $contact->id,
            'proposal_status_id' => $status->id,
        ]);

        $this->actingAs($client, 'client');
        $response = $this->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('proposals')
            ->has('invoices')
        );
    }

    public function test_guest_cannot_access_portal_dashboard()
    {
        $response = $this->get(route('portal.dashboard'));
        $response->assertRedirect(route('portal.login'));
    }
}

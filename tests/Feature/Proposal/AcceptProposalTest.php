<?php

namespace Tests\Feature\Proposal;

use App\Actions\AcceptProposal;
use App\Jobs\CreateInvoiceFromProposal;
use App\Jobs\ProvisionProjectFromProposal;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AcceptProposalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->account = Account::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $role = Role::create(['name' => 'admin']);
        $this->user->addRole($role, $this->workspace);

        $this->actingAs($this->user)
            ->withSession(['current_workspace_id' => $this->workspace->id]);
    }

    public function test_accept_proposal_dispatches_automation_jobs(): void
    {
        Bus::fake([CreateInvoiceFromProposal::class, ProvisionProjectFromProposal::class]);

        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'title' => 'Automation Test Proposal',
            'currency' => 'USD',
        ]);

        ProposalItem::factory()->create([
            'proposal_id' => $proposal->id,
            'item_name' => 'Service A',
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_tax_amount' => 100,
            'total' => 1100,
        ]);

        $action = app(AcceptProposal::class);
        $action->accept($proposal, [
            'name' => 'John Doe',
            'signature' => 'signature-data',
        ]);

        Bus::assertDispatched(CreateInvoiceFromProposal::class, function ($job) use ($proposal) {
            return $job->proposal->id === $proposal->id;
        });

        Bus::assertDispatched(ProvisionProjectFromProposal::class, function ($job) use ($proposal) {
            return $job->proposal->id === $proposal->id;
        });

        $proposal->refresh();
        $this->assertNotNull($proposal->accepted_at);
        $this->assertNotNull($proposal->decided_at);
        $this->assertNotNull($proposal->signed_at);
        $this->assertEquals('John Doe', $proposal->signer_name);
        $this->assertEquals(['data' => 'signature-data'], $proposal->signature_data);
    }

    public function test_proposal_acceptance_creates_linked_project_and_invoice(): void
    {
        Bus::fake([CreateInvoiceFromProposal::class, ProvisionProjectFromProposal::class]);

        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'title' => 'Full Integration Proposal',
            'currency' => 'USD',
        ]);

        ProposalItem::factory()->create([
            'proposal_id' => $proposal->id,
            'item_name' => 'Service B',
            'unit_price' => 2000,
            'quantity' => 1,
            'subtotal' => 2000,
            'discount_amount' => 0,
            'total_tax_amount' => 200,
            'total' => 2200,
        ]);

        $action = app(AcceptProposal::class);
        $action->accept($proposal, [
            'name' => 'Jane Doe',
            'signature' => 'sig-data',
        ]);

        $proposal->refresh();
        $this->assertNotNull($proposal->accepted_at);

        // Run project provisioning job first so invoice can link to it
        $projectJob = new ProvisionProjectFromProposal($proposal);
        $projectJob->handle();

        $proposal->refresh();
        $this->assertNotNull($proposal->project_id);

        $project = $proposal->project;
        $this->assertNotNull($project);
        $this->assertEquals('Full Integration Proposal', $project->name);
        $this->assertEquals($this->workspace->id, $project->workspace_id);
        $this->assertEquals($this->account->id, $project->account_id);

        // Run invoice creation job with refreshed proposal so it sees the project
        $proposal->refresh();
        $invoiceJob = new CreateInvoiceFromProposal($proposal);
        $invoiceJob->handle(app(InvoiceService::class));

        $this->assertDatabaseHas('invoices', [
            'proposal_id' => $proposal->id,
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'project_id' => $project->id,
        ]);

        $invoice = Invoice::where('proposal_id', $proposal->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($project->id, $invoice->project_id);
        $this->assertEquals(2000, $invoice->subtotal);
        $this->assertEquals(200, $invoice->total_tax_amount);
        $this->assertEquals(2200, $invoice->grand_total);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'Service B',
            'unit_price' => 2000,
            'subtotal' => 2000,
        ]);
    }

    public function test_accept_proposal_sets_proposal_status_to_accepted(): void
    {
        $acceptedStatus = ProposalStatus::where('workspace_id', $this->workspace->id)
            ->where('automation_trigger', 'accepted')
            ->first();

        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'title' => 'Status Test',
            'currency' => 'USD',
        ]);

        $action = app(AcceptProposal::class);
        $action->accept($proposal, ['name' => 'Test User']);

        $proposal->refresh();
        $this->assertEquals($acceptedStatus->id, $proposal->proposal_status_id);
    }

    public function test_provision_project_job_skips_when_project_already_exists(): void
    {
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'title' => 'Existing Project Proposal',
            'currency' => 'USD',
        ]);

        $action = app(AcceptProposal::class);
        $action->accept($proposal, ['name' => 'Test', 'signature' => 'sig']);

        // Sync queue may have already created the project; reload to see it
        $proposal->refresh();

        // Run project job - should skip if project already exists
        $projectJob = new ProvisionProjectFromProposal($proposal);
        $projectJob->handle();

        $proposal->refresh();
        $firstProjectId = $proposal->project_id;
        $this->assertNotNull($firstProjectId);

        // Run project job again - should skip
        $projectJob2 = new ProvisionProjectFromProposal($proposal);
        $projectJob2->handle();

        $proposal->refresh();
        $this->assertEquals($firstProjectId, $proposal->project_id);
        $this->assertCount(1, Project::where('workspace_id', $this->workspace->id)->get());
    }
}

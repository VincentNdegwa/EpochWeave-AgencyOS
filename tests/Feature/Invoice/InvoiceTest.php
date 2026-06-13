<?php

namespace Tests\Feature\Invoice;

use App\Enums\InvoiceStatus;
use App\Jobs\CreateInvoiceFromProposal;
use App\Jobs\ProvisionProjectFromProposal;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create([
            'currency' => 'USD',
        ]);
        $this->account = Account::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $role = Role::create(['name' => 'admin']);
        $this->user->addRole($role, $this->workspace);

        $this->actingAs($this->user)
            ->withSession(['current_workspace_id' => $this->workspace->id]);
    }

    public function test_it_can_create_invoice_with_line_items(): void
    {
        $product = Product::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $invoiceData = [
            'account_id' => $this->account->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'line_items' => [
                [
                    'id' => 'item-1',
                    'item_name' => 'Web Design',
                    'description' => 'Complete web design',
                    'unit_label' => 'Pcs',
                    'quantity' => 2,
                    'unit_price' => 500,
                    'subtotal' => 1000,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'tax_type' => 'none',
                    'tax_value' => 0,
                    'product_id' => $product->id,
                ],
            ],
        ];

        $response = $this->post(route('invoices.store'), $invoiceData);

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'account_id' => $this->account->id,
            'workspace_id' => $this->workspace->id,
            'status' => 'draft',
            'currency' => 'USD',
        ]);

        $invoice = Invoice::where('workspace_id', $this->workspace->id)
            ->where('account_id', $this->account->id)
            ->first();
        $this->assertCount(1, $invoice->items);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'Web Design',
            'unit_price' => 500,
            'quantity' => 2,
            'subtotal' => 1000,
        ]);
    }

    public function test_it_can_create_invoice_from_proposal(): void
    {
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
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

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createInvoiceFromProposal($proposal);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'proposal_id' => $proposal->id,
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'Service A',
            'unit_price' => 1000,
            'subtotal' => 1000,
            'total_tax_amount' => 100,
            'total' => 1100,
        ]);

        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals(100, $invoice->total_tax_amount);
        $this->assertEquals(1100, $invoice->grand_total);
    }

    public function test_it_creates_invoice_from_proposal_with_project(): void
    {
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'currency' => 'USD',
        ]);

        ProposalItem::factory()->create([
            'proposal_id' => $proposal->id,
            'item_name' => 'Service B',
            'unit_price' => 2000,
            'quantity' => 1,
            'subtotal' => 2000,
            'total' => 2000,
        ]);

        $project = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createInvoiceFromProposal($proposal, $project);

        $this->assertEquals($project->id, $invoice->project_id);
    }

    public function test_create_invoice_from_proposal_job_dispatches(): void
    {
        Bus::fake([CreateInvoiceFromProposal::class]);

        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        CreateInvoiceFromProposal::dispatch($proposal);

        Bus::assertDispatched(CreateInvoiceFromProposal::class, function ($job) use ($proposal) {
            return $job->proposal->id === $proposal->id;
        });
    }

    public function test_provision_project_from_proposal_job_creates_project(): void
    {
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'title' => 'Test Project Proposal',
            'currency' => 'USD',
        ]);

        $job = new ProvisionProjectFromProposal($proposal);
        $job->handle();

        $this->assertDatabaseHas('projects', [
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'name' => 'Test Project Proposal',
        ]);

        $proposal->refresh();
        $this->assertNotNull($proposal->project_id);
    }

    public function test_provision_project_job_skips_if_project_exists(): void
    {
        $existingProject = Project::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'project_id' => $existingProject->id,
        ]);

        $job = new ProvisionProjectFromProposal($proposal);
        $job->handle();

        $this->assertEquals($existingProject->id, $proposal->fresh()->project_id);
    }

    public function test_it_can_update_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
            'invoice_number' => 'INV-OLD',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Old Item',
        ]);

        $updateData = [
            'account_id' => $this->account->id,
            'invoice_number' => 'INV-OLD',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'line_items' => [
                [
                    'id' => 'item-new',
                    'item_name' => 'New Item',
                    'description' => 'New description',
                    'unit_label' => 'Pcs',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'subtotal' => 200,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'tax_type' => 'none',
                    'tax_value' => 0,
                ],
            ],
        ];

        $response = $this->put(route('invoices.update', $invoice->id), $updateData);
        $response->assertRedirect();

        $this->assertDatabaseMissing('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'Old Item',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'New Item',
        ]);
    }

    public function test_it_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        $response = $this->delete(route('invoices.destroy', $invoice->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id,
        ]);
    }

    public function test_invoice_status_enum_values(): void
    {
        $this->assertEquals('draft', InvoiceStatus::Draft->value);
        $this->assertEquals('sent', InvoiceStatus::Sent->value);
        $this->assertEquals('paid', InvoiceStatus::Paid->value);
        $this->assertEquals('void', InvoiceStatus::Void->value);
        $this->assertEquals('overdue', InvoiceStatus::Overdue->value);
    }
}

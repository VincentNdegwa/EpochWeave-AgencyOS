<?php

namespace Tests\Feature\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);

        $status = InvoiceStatus::where('workspace_id', $this->workspace->id)
            ->where('automation_trigger', 'sent')
            ->firstOrFail();

        $this->invoice = Invoice::factory()->create([
            'workspace_id' => $this->workspace->id,
            'invoice_status_id' => $status->id,
            'grand_total' => 100000,
            'amount_paid' => 0,
        ]);

        $this->actingAs($this->user);
    }

    public function test_user_can_record_payment()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('invoices.payments.store', $this->invoice), [
                'amount' => 50000,
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
                'reference' => 'REF-123',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount' => 50000,
            'method' => 'bank_transfer',
            'reference' => 'REF-123',
        ]);
    }

    public function test_payment_updates_invoice_amount_paid()
    {
        $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('invoices.payments.store', $this->invoice), [
                'amount' => 50000,
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
            ]);

        $this->invoice->refresh();
        $this->assertEquals(50000, $this->invoice->amount_paid);
    }

    public function test_full_payment_updates_status_to_paid()
    {
        $paidStatus = InvoiceStatus::where('workspace_id', $this->workspace->id)
            ->where('automation_trigger', 'paid')
            ->firstOrFail();

        $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('invoices.payments.store', $this->invoice), [
                'amount' => 100000,
                'method' => 'credit_card',
                'paid_at' => now()->toDateString(),
            ]);

        $this->invoice->refresh();
        $this->assertEquals($paidStatus->id, $this->invoice->invoice_status_id);
        $this->assertNotNull($this->invoice->paid_at);
    }

    public function test_user_can_delete_payment()
    {
        $payment = Payment::factory()->create([
            'workspace_id' => $this->workspace->id,
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
            'amount' => 30000,
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->delete(route('invoices.payments.destroy', [$this->invoice, $payment]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_amount_is_required_for_payment()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('invoices.payments.store', $this->invoice), [
                'method' => 'cash',
                'paid_at' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('amount');
    }
}

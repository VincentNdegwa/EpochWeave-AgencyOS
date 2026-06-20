<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreditNoteService
{
    public function __construct(private ActivityService $activityService) {}

    public function recordRefund(Invoice $invoice, array $data): CreditNote
    {
        try {
            return DB::transaction(function () use ($invoice, $data) {
                $creditNote = CreditNote::create([
                    'workspace_id' => $invoice->workspace_id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $data['payment_id'] ?? null,
                    'user_id' => $data['user_id'],
                    'amount' => $data['amount'],
                    'reason' => $data['reason'] ?? null,
                    'refunded_at' => $data['refunded_at'] ?? now(),
                    'reference' => $data['reference'] ?? null,
                ]);

                $this->updateInvoiceAfterRefund($invoice);

                $this->activityService->record(
                    $invoice,
                    'invoice.refund_recorded',
                    'Refund of '.$data['amount'].' recorded on invoice.'
                );

                if ($invoice->account_id) {
                    $invoice->account()->decrement('lifetime_value', $data['amount']);
                }

                return $creditNote->load(['user', 'payment']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to record refund: '.$e->getMessage());
        }
    }

    private function updateInvoiceAfterRefund(Invoice $invoice): void
    {
        $totalRefunded = CreditNote::where('invoice_id', $invoice->id)->sum('amount');
        $totalPaid = Payment::where('invoice_id', $invoice->id)->sum('amount');
        $netPaid = max(0, $totalPaid - $totalRefunded);

        $invoice->update(['amount_paid' => $netPaid]);

        $paidStatus = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
            ->where('automation_trigger', 'paid')
            ->first();
        $sentStatus = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
            ->where('automation_trigger', 'sent')
            ->first();

        if ($paidStatus && $netPaid >= $invoice->grand_total) {
            $invoice->update([
                'invoice_status_id' => $paidStatus->id,
                'paid_at' => now(),
            ]);
        } elseif ($sentStatus && $netPaid < $invoice->grand_total && $netPaid > 0) {
            $invoice->update([
                'invoice_status_id' => $sentStatus->id,
                'paid_at' => null,
            ]);
        } elseif ($sentStatus && $netPaid === 0 && $invoice->invoice_status_id === $paidStatus?->id) {
            $invoice->update([
                'invoice_status_id' => $sentStatus->id,
                'paid_at' => null,
            ]);
        }
    }
}

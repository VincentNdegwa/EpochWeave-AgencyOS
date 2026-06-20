<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    public function __construct(private ActivityService $activityService) {}

    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        try {
            return DB::transaction(function () use ($invoice, $data) {
                $payment = Payment::create([
                    'workspace_id' => $invoice->workspace_id,
                    'invoice_id' => $invoice->id,
                    'user_id' => $data['user_id'],
                    'amount' => $data['amount'],
                    'method' => $data['method'] ?? 'bank_transfer',
                    'paid_at' => $data['paid_at'] ?? now(),
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $this->updateInvoicePaymentState($invoice);
                $this->activityService->record($invoice, 'invoice.payment_recorded', "Payment of {$data['amount']} was recorded on invoice.");

                if ($invoice->account_id) {
                    $invoice->account()->increment('lifetime_value', $data['amount']);
                }

                return $payment->load('user');
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to record payment: '.$e->getMessage(), 0, $e);
        }
    }

    public function deletePayment(Payment $payment): void
    {
        try {
            DB::transaction(function () use ($payment) {
                $invoice = $payment->invoice;
                $payment->delete();
                $this->activityService->record($invoice, 'invoice.payment_deleted', 'A payment was deleted from invoice.');
                $this->updateInvoicePaymentState($invoice);

                if ($invoice->account_id) {
                    $invoice->account()->decrement('lifetime_value', $payment->amount);
                }
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to delete payment: '.$e->getMessage(), 0, $e);
        }
    }

    private function updateInvoicePaymentState(Invoice $invoice): void
    {
        $totalPaid = Payment::where('invoice_id', $invoice->id)->sum('amount');
        $invoice->update(['amount_paid' => $totalPaid]);

        $paidStatus = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
            ->where('automation_trigger', 'paid')
            ->first();

        if ($paidStatus && $totalPaid >= $invoice->grand_total) {
            $invoice->update([
                'invoice_status_id' => $paidStatus->id,
                'paid_at' => now(),
            ]);
        } elseif ($paidStatus && $totalPaid > 0 && $totalPaid < $invoice->grand_total) {
            $sentStatus = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
                ->where('automation_trigger', 'sent')
                ->first();

            if ($sentStatus && $invoice->invoice_status_id === $paidStatus->id) {
                $invoice->update([
                    'invoice_status_id' => $sentStatus->id,
                    'paid_at' => null,
                ]);
            }
        }
    }
}

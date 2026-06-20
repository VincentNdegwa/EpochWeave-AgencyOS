<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($invoice->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'in:bank_transfer,credit_card,cash,check,other'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;

        try {
            $this->paymentService->recordPayment($invoice, $validated);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Invoice $invoice, Payment $payment): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($invoice->workspace_id !== $workspace->id || $payment->invoice_id !== $invoice->id) {
            abort(404);
        }

        try {
            $this->paymentService->deletePayment($payment);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}

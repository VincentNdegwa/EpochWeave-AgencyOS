<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\CreditNoteService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditNoteController extends Controller
{
    public function __construct(
        private CreditNoteService $creditNoteService,
    ) {}

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($invoice->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'refunded_at' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;

        try {
            $this->creditNoteService->recordRefund($invoice, $validated);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Refund recorded successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceStatusRequest;
use App\Http\Requests\UpdateInvoiceStatusRequest;
use App\Models\InvoiceStatus;
use App\Services\InvoiceStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceStatusController extends Controller
{
    public function __construct(private readonly InvoiceStatusService $invoiceStatusService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $statuses = $this->invoiceStatusService->listForWorkspace($workspace->id);

        return Inertia::render('invoice-status/index', [
            'invoice_statuses' => $statuses,
        ]);
    }

    public function store(StoreInvoiceStatusRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->invoiceStatusService->createStatus($data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice status created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateInvoiceStatusRequest $request, InvoiceStatus $invoiceStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($invoiceStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->invoiceStatusService->updateStatus($invoiceStatus, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice status updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, InvoiceStatus $invoiceStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($invoiceStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->invoiceStatusService->deleteStatus($invoiceStatus);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice status deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}

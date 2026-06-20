<?php

namespace App\Http\Controllers;

use App\Actions\SendInvoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Activity;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\WorkspaceSetting;
use App\Notifications\InvoiceLateReminder;
use App\Services\ActivityService;
use App\Services\InvoiceService;
use App\Services\UserPreferenceService;
use App\Services\WorkspaceSettingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private UserPreferenceService $userPreferenceService,
        private WorkspaceSettingService $workspaceSettingService,
        private SendInvoice $sendInvoice,
        private ActivityService $activityService,
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $user = $request->user();

        $result = $this->invoiceService->getFilteredInvoices(
            $workspace->id,
            $request->query('status'),
            $request->query('search'),
        );
        $displayMode = $this->userPreferenceService->getDisplayMode($workspace->id, $user->id);

        $invoiceStatuses = InvoiceStatus::where('workspace_id', $workspace->id)
            ->orderBy('position')
            ->get();

        return Inertia::render('invoices/index', [
            'invoices' => $result['invoices'],
            'display_mode' => $displayMode,
            'invoice_statuses' => $invoiceStatuses,
            'filters' => [
                'status' => $request->query('status', 'all'),
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('invoices/create');
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = $request->validated();

            $settings = $this->workspaceSettingService->getOrCreate(
                $workspace->id,
                WorkspaceSetting::SUBMODULE_INVOICES
            );

            $numberingSettings = $settings->settings['numbering'] ?? [];
            $nextSequenceNumber = (int) ($numberingSettings['next_sequence_number'] ?? 1);
            $invoiceNumber = $this->generateInvoiceNumber($numberingSettings);

            // Get draft status for this workspace
            $draftStatus = InvoiceStatus::where('workspace_id', $workspace->id)
                ->where('automation_trigger', 'draft')
                ->first();

            $data['workspace_id'] = $workspace->id;
            $data['created_by'] = $request->user()->id;
            $data['user_id'] = $request->user()->id;
            $data['invoice_number'] = $invoiceNumber;
            $data['invoice_status_id'] = $draftStatus?->id;
            $data['currency'] = $workspace->currency ?? 'USD';
            $data['token'] = Str::uuid();

            $lineItems = $data['line_items'] ?? [];
            unset($data['line_items']);

            $invoice = $this->invoiceService->createInvoiceWithItems($data, $lineItems);

            $this->workspaceSettingService->incrementNumberingSequence($settings, $nextSequenceNumber);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice created successfully.']);

            return redirect()->route('invoices.edit', $invoice->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function show(int $id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);

        if (! $invoice) {
            abort(404);
        }

        $activities = Activity::where('subject_type', Invoice::class)
            ->where('subject_id', $invoice->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $invoiceStatuses = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
            ->orderBy('position')
            ->get();

        return Inertia::render('invoices/show', [
            'invoice' => $invoice,
            'activities' => $activities,
            'invoice_statuses' => $invoiceStatuses,
        ]);
    }

    public function edit(int $id)
    {
        $invoice = $this->invoiceService->getInvoiceById($id);

        if (! $invoice) {
            abort(404);
        }

        return Inertia::render('invoices/edit', [
            'invoice' => $invoice,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, int $id): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            if (! $invoice) {
                abort(404);
            }

            $data = $request->validated();
            $lineItems = $data['line_items'] ?? [];
            unset($data['line_items']);

            $this->invoiceService->updateInvoice($invoice, $data, $lineItems);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice updated successfully.']);

            return redirect()->route('invoices.show', $invoice->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            if (! $invoice) {
                abort(404);
            }

            $this->invoiceService->deleteInvoice($invoice);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice deleted successfully.']);

            return redirect()->route('invoices.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function publicShow(string $token)
    {
        $invoice = Invoice::where('token', $token)
            ->with(['account', 'items', 'accountContact', 'user', 'workspace', 'invoiceStatus'])
            ->firstOrFail();

        $invoice->update([
            'view_count' => ($invoice->view_count ?? 0) + 1,
            'last_viewed_at' => now(),
        ]);

        $this->activityService->viewed($invoice, "Invoice '{$invoice->invoice_number}' was viewed by a visitor.");

        return Inertia::render('public/invoice/show', [
            'invoice' => $invoice,
        ]);
    }

    public function send(int $id): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            if (! $invoice) {
                abort(404);
            }

            $this->sendInvoice->send($invoice);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice sent successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function sendReminder(Request $request, int $id): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $invoice = Invoice::where('workspace_id', $workspace->id)
                ->with('accountContact')
                ->findOrFail($id);

            $contact = $invoice->accountContact;

            if ($contact) {
                $contact->notify(new InvoiceLateReminder($invoice));
            }

            $this->activityService->record($invoice, 'invoice.reminder_sent', 'Late payment reminder sent to '.$contact?->email.'.');

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Reminder sent successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function downloadReceipt(Request $request, int $id)
    {
        $workspace = $request->attributes->get('current_workspace');
        $invoice = Invoice::where('workspace_id', $workspace->id)
            ->with(['account', 'items', 'accountContact', 'user', 'workspace', 'invoiceStatus', 'payments.user'])
            ->findOrFail($id);

        return response()->view('invoices.receipt', [
            'invoice' => $invoice,
        ])->header('Content-Type', 'text/html');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        try {
            $request->validate([
                'invoice_status_id' => 'required|integer|exists:invoice_statuses,id',
            ]);

            $workspace = $request->attributes->get('current_workspace');
            $status = InvoiceStatus::where('workspace_id', $workspace->id)
                ->where('id', $request->input('invoice_status_id'))
                ->firstOrFail();

            if (in_array($status->automation_trigger, ['paid', 'overdue'])) {
                Inertia::flash('toast', ['type' => 'error', 'message' => 'The '.$status->title.' status is system-controlled and cannot be set manually.']);

                return redirect()->back();
            }

            $invoice = $this->invoiceService->getInvoiceById($id);

            if (! $invoice) {
                abort(404);
            }

            if ($status->automation_trigger === 'sent') {
                $this->sendInvoice->send($invoice);
            } else {
                $updateData = ['invoice_status_id' => $status->id];

                if ($status->automation_trigger === 'voided') {
                    $updateData['voided_at'] = now();
                }

                $invoice->update($updateData);
            }

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice status updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    private function generateInvoiceNumber(array $numberingSettings): string
    {
        $format = $numberingSettings['format'] ?? '{PREFIX}{DELIMITER}{SEQUENCE}';
        $prefix = $numberingSettings['prefix'] ?? 'INV';
        $delimiter = $numberingSettings['delimiter'] ?? '-';
        $sequencePadding = max(0, (int) ($numberingSettings['sequence_padding'] ?? 4));
        $nextSequenceNumber = (int) ($numberingSettings['next_sequence_number'] ?? 1);

        $sequence = $sequencePadding > 0
            ? str_pad((string) $nextSequenceNumber, $sequencePadding, '0', STR_PAD_LEFT)
            : (string) $nextSequenceNumber;

        return strtr($format, [
            '{PREFIX}' => $prefix,
            '{YEAR}' => now()->format('Y'),
            '{DELIMITER}' => $delimiter,
            '{SEQUENCE}' => $sequence,
        ]);
    }

    public function duplicate(Request $request, int $id): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $invoice = Invoice::where('workspace_id', $workspace->id)->findOrFail($id);

            $settings = $this->workspaceSettingService->getOrCreate(
                $workspace->id,
                WorkspaceSetting::SUBMODULE_INVOICES
            );

            $numberingSettings = $settings->settings['numbering'] ?? [];
            $nextSequenceNumber = (int) ($numberingSettings['next_sequence_number'] ?? 1);
            $newInvoiceNumber = $this->generateInvoiceNumber($numberingSettings);

            $draftStatus = InvoiceStatus::where('workspace_id', $workspace->id)
                ->where('automation_trigger', 'draft')
                ->first();

            $newInvoice = $this->invoiceService->duplicateInvoice(
                $invoice,
                $draftStatus?->id ?? 0,
                $newInvoiceNumber
            );

            $this->workspaceSettingService->incrementNumberingSequence($settings, $nextSequenceNumber);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Invoice duplicated successfully.']);

            return redirect()->route('invoices.edit', $newInvoice->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:invoices,id',
            ]);

            $ids = $request->ids;
            $workspace = $request->attributes->get('current_workspace');

            $invoices = Invoice::where('workspace_id', $workspace->id)
                ->whereIn('id', $ids)
                ->with('invoiceStatus')
                ->get();

            $draftIds = $invoices->filter(
                fn ($i) => $i->invoiceStatus?->automation_trigger === 'draft'
            )->pluck('id')->toArray();
            $skippedCount = $invoices->reject(
                fn ($i) => $i->invoiceStatus?->automation_trigger === 'draft'
            )->count();

            if (! empty($draftIds)) {
                Invoice::whereIn('id', $draftIds)->delete();
            }

            $deletedCount = count($draftIds);

            if ($deletedCount > 0 && $skippedCount > 0) {
                Inertia::flash('toast', ['type' => 'success', 'message' => "{$deletedCount} draft invoice(s) deleted. {$skippedCount} non-draft invoice(s) were skipped."]);
            } elseif ($deletedCount > 0) {
                Inertia::flash('toast', ['type' => 'success', 'message' => "{$deletedCount} draft invoice(s) deleted successfully."]);
            } else {
                Inertia::flash('toast', ['type' => 'info', 'message' => 'No invoices deleted. Only draft invoices can be deleted.']);
            }

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}

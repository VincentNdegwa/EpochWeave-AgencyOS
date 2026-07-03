<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Exceptions\AccountException;
use App\Http\Requests\Imports\ImportAccountsRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Activity;
use App\Models\CompanySize;
use App\Models\Industry;
use App\Models\LeadSource;
use App\Services\AccountImportService;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private AccountImportService $importService,
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');

        $result = $this->accountService->getFilteredAccounts(
            $workspace->id,
            $request->query('status'),
            $request->query('search'),
            $request->query('date_from'),
            $request->query('date_to')
        );

        return Inertia::render('account/index', [
            'accounts' => $result['accounts'],
            'stats' => $result['stats'],
            'filters' => [
                'status' => $request->query('status', 'all'),
                'search' => $request->query('search'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
        $account = $this->accountService->createAccount($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Account created successfully.']);

        return redirect()->route('accounts.show', $account);
    }

    public function show(Request $request, int $id)
    {
        $workspace = $request->attributes->get('current_workspace');
        $account = $this->accountService->getAccountById($id);

        if (! $account) {
            abort(404);
        }

        $account->load(['contacts', 'addresses', 'socialProfiles', 'industry', 'leadSource', 'companySize']);

        $activities = Activity::where('subject_type', Account::class)
            ->where('subject_id', $account->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return Inertia::render('account/show', [
            'account' => $account,
            'activities' => $activities,
        ]);
    }

    public function update(UpdateAccountRequest $request, int $id): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($id);

            if (! $account) {
                abort(404);
            }

            $this->accountService->updateAccount($account, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Account updated successfully.']);

            return redirect()->route('accounts.show', $account->id);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($id);

            if (! $account) {
                abort(404);
            }

            $this->accountService->deleteAccount($account);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Account deleted successfully.']);

            return redirect()->route('accounts.index');
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:accounts,id',
            'status' => 'required|string|in:'.implode(',', array_column(AccountStatus::cases(), 'value')),
        ]);

        try {
            $status = AccountStatus::from($request->input('status'));
            $updated = $this->accountService->bulkUpdateStatus($request->input('ids'), $status);

            Inertia::flash('toast', ['type' => 'success', 'message' => "Successfully updated {$updated} accounts."]);

            return redirect()->back();
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to update accounts.']);

            return redirect()->back();
        }
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:accounts,id',
        ]);

        try {
            $deleted = $this->accountService->bulkDelete($request->input('ids'));

            Inertia::flash('toast', ['type' => 'success', 'message' => "Successfully deleted {$deleted} accounts."]);

            return redirect()->back();
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to delete accounts.']);

            return redirect()->back();
        }
    }

    public function importPage(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('account/import', [
            'industries' => Industry::where('workspace_id', $workspace->id)->orderBy('name')->get(['id', 'name']),
            'lead_sources' => LeadSource::where('workspace_id', $workspace->id)->orderBy('name')->get(['id', 'name']),
            'company_sizes' => CompanySize::where('workspace_id', $workspace->id)->orderBy('sort_order')->get(['id', 'label']),
        ]);
    }

    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:2048'],
        ]);

        $path = $request->file('file')->getRealPath();
        $preview = $this->importService->parsePreview($path);

        return response()->json($preview);
    }

    public function import(ImportAccountsRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $result = $this->importService->importFromData(
                $workspace->id,
                $request->validated('accounts'),
            );

            $message = "Successfully imported {$result['created']} accounts.";
            if ($result['errors'] !== []) {
                $message .= ' '.count($result['errors']).' rows failed.';
            }

            Inertia::flash('toast', [
                'type' => $result['errors'] === [] ? 'success' : 'warning',
                'message' => $message,
                'errors' => $result['errors'],
            ]);
        } catch (\Exception $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Import failed: '.$e->getMessage(),
            ]);
        }

        return redirect()->route('accounts.index');
    }

    public function downloadImportTemplate(): Response
    {
        $headers = [
            'company_name',
            'phone',
            'website',
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_phone',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $sheet->fromArray([
            'Acme Corp',
            '+1 555 1234',
            'https://acme.example.com',
            'John',
            'Doe',
            'john@acme.example.com',
            '+1 555 5678',
        ], null, 'A2', true);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tmpPath = tempnam(sys_get_temp_dir(), 'accounts_import_template_');
        $writer->save($tmpPath);
        $content = file_get_contents($tmpPath);
        unlink($tmpPath);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="accounts_import_template.xlsx"',
        ]);
    }
}

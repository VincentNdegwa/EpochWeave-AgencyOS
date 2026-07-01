<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Exceptions\AccountException;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Activity;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
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
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\AccountException;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
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

    public function create(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('account/create', [
            'workspace_id' => $workspace->id,
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

    public function show(int $id)
    {
        $account = $this->accountService->getAccountById($id);

        if (! $account) {
            abort(404);
        }

        return Inertia::render('account/show', [
            'account' => $account,
        ]);
    }

    public function edit(int $id)
    {
        $account = $this->accountService->getAccountById($id);

        if (! $account) {
            abort(404);
        }

        return Inertia::render('account/edit', [
            'account' => $account,
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
}

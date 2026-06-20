<?php

namespace App\Http\Controllers;

use App\Exceptions\AccountException;
use App\Http\Requests\StoreAccountContactRequest;
use App\Http\Requests\UpdateAccountContactRequest;
use App\Models\AccountContact;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AccountContactController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    public function store(StoreAccountContactRequest $request, int $accountId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = $this->accountService->createContact($account, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact created successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateAccountContactRequest $request, int $accountId, int $contactId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = AccountContact::where('account_id', $account->id)->where('id', $contactId)->first();

            if (! $contact) {
                abort(404);
            }

            $this->accountService->updateContact($contact, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact updated successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $accountId, int $contactId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = AccountContact::where('account_id', $account->id)->where('id', $contactId)->first();

            if (! $contact) {
                abort(404);
            }

            $this->accountService->deleteContact($contact);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact deleted successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function setPrimary(int $accountId, int $contactId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = AccountContact::where('account_id', $account->id)->where('id', $contactId)->first();

            if (! $contact) {
                abort(404);
            }

            $this->accountService->setPrimaryContact($contact);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact set as primary successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function grantPortalAccess(int $accountId, int $contactId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = AccountContact::where('account_id', $account->id)->where('id', $contactId)->first();

            if (! $contact) {
                abort(404);
            }

            $this->accountService->grantPortalAccess($account, $contact);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Portal invitation sent successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function revokePortalAccess(int $accountId, int $contactId): RedirectResponse
    {
        try {
            $account = $this->accountService->getAccountById($accountId);

            if (! $account) {
                abort(404);
            }

            $contact = AccountContact::where('account_id', $account->id)->where('id', $contactId)->first();

            if (! $contact) {
                abort(404);
            }

            $this->accountService->revokePortalAccess($contact);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Portal access revoked successfully.']);

            return redirect()->route('accounts.show', $accountId);
        } catch (AccountException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}

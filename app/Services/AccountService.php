<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Exceptions\AccountException;
use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountService
{
    public function createAccount(array $data): Account
    {
        try {
            return DB::transaction(function () use ($data) {
                $account = Account::create([
                    'workspace_id' => $data['workspace_id'],
                    'company_name' => $data['company_name'],
                    'website' => $data['website'] ?? null,
                    'status' => $data['status'] ?? AccountStatus::Lead->value,
                    'token' => Str::random(32),
                    'lifetime_value' => $data['lifetime_value'] ?? 0,
                ]);

                if (isset($data['contacts']) && is_array($data['contacts'])) {
                    foreach ($data['contacts'] as $contactData) {
                        $this->createContact($account, $contactData);
                    }
                }

                return $account;
            });
        } catch (\Exception $e) {
            throw AccountException::accountCreationFailed($e->getMessage());
        }
    }

    public function updateAccount(Account $account, array $data): Account
    {
        try {
            $account->update([
                'company_name' => $data['company_name'] ?? $account->company_name,
                'website' => $data['website'] ?? $account->website,
                'status' => $data['status'] ?? $account->status,
                'lifetime_value' => $data['lifetime_value'] ?? $account->lifetime_value,
            ]);

            return $account;
        } catch (\Exception $e) {
            throw AccountException::accountUpdateFailed($e->getMessage());
        }
    }

    public function deleteAccount(Account $account): void
    {
        try {
            $account->delete();
        } catch (\Exception $e) {
            throw AccountException::cannotDeleteAccount();
        }
    }

    public function createContact(Account $account, array $data): AccountContact
    {
        try {
            $contact = AccountContact::create([
                'account_id' => $account->id,
                'client_profile_id' => $data['client_profile_id'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'is_verified' => $data['is_verified'] ?? false,
                'is_primary' => $data['is_primary'] ?? false,
                'receives_billing' => $data['receives_billing'] ?? true,
            ]);

            if ($contact->is_primary) {
                $account->contacts()
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            if (isset($data['send_invitation']) && $data['send_invitation']) {
                $this->createInvitation($account, $contact);
            }

            return $contact;
        } catch (\Exception $e) {
            throw AccountException::contactCreationFailed($e->getMessage());
        }
    }

    public function createInvitation(Account $account, AccountContact $contact): PortalInvitation
    {
        return PortalInvitation::create([
            'account_id' => $account->id,
            'account_contact_id' => $contact->id,
            'invitation_token' => Str::random(64),
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function updateContact(AccountContact $contact, array $data): AccountContact
    {
        try {
            $contact->update([
                'first_name' => $data['first_name'] ?? $contact->first_name,
                'last_name' => $data['last_name'] ?? $contact->last_name,
                'email' => $data['email'] ?? $contact->email,
                'phone' => $data['phone'] ?? $contact->phone,
                'job_title' => $data['job_title'] ?? $contact->job_title,
                'is_verified' => $data['is_verified'] ?? $contact->is_verified,
                'is_primary' => $data['is_primary'] ?? $contact->is_primary,
                'receives_billing' => $data['receives_billing'] ?? $contact->receives_billing,
            ]);

            if ($contact->is_primary) {
                $contact->account->contacts()
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            return $contact;
        } catch (\Exception $e) {
            throw AccountException::contactUpdateFailed($e->getMessage());
        }
    }

    public function deleteContact(AccountContact $contact): void
    {
        try {
            $contact->delete();
        } catch (\Exception $e) {
            throw AccountException::contactNotFound();
        }
    }

    public function getAllAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::with('contacts')->get();
    }

    public function getAccountsByWorkspace(int $workspaceId): \Illuminate\Database\Eloquent\Collection
    {
        return Account::with('contacts')->where('workspace_id', $workspaceId)->get();
    }

    public function getAccountById(int $id): ?Account
    {
        return Account::with('contacts')->find($id);
    }
}

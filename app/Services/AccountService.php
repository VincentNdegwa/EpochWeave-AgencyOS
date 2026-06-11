<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Exceptions\AccountException;
use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
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
        if ($account->contacts()->exists()) {
            throw AccountException::cannotDeleteAccount();
        }

        try {
            $deleted = (bool) $account->delete();
        } catch (\Throwable $e) {
            $deleted = false;
        }

        if (! $deleted) {
            Account::query()->whereKey($account->getKey())->delete();
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

    public function getAllAccounts(): Collection
    {
        return Account::with('contacts')->get();
    }

    public function getAccountsByWorkspace(int $workspaceId): Collection
    {
        return Account::with('contacts')->where('workspace_id', $workspaceId)->get();
    }

    public function getFilteredAccounts(int $workspaceId, ?string $status = null, ?string $search = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Account::query()->where('workspace_id', $workspaceId);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('company_name', 'like', "%{$search}%");
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $accounts = $query->get();

        $stats = $this->getAccountStats($workspaceId, $dateFrom, $dateTo);

        return [
            'accounts' => $accounts,
            'stats' => $stats,
        ];
    }

    private function getAccountStats(int $workspaceId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        if (! $dateFrom && ! $dateTo) {
            $currentFrom = now()->startOfMonth()->startOfDay();
            $currentTo = now()->endOfDay();

            $previousTo = now()->subMonthNoOverflow()->endOfDay();
            $previousFrom = $previousTo->copy()->startOfMonth()->startOfDay();

            $label = 'last month';
        } else {
            $currentFrom = Carbon::parse($dateFrom ?? $dateTo)->startOfDay();
            $currentTo = Carbon::parse($dateTo ?? $dateFrom)->endOfDay();

            $days = $currentFrom->diffInDays($currentTo) + 1;
            $previousTo = $currentFrom->copy()->subDay()->endOfDay();
            $previousFrom = $previousTo->copy()->subDays($days - 1)->startOfDay();

            $label = 'previous period';
        }

        $currentCounts = Account::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$currentFrom, $currentTo])
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $previousCounts = Account::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$previousFrom, $previousTo])
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $currentTotal = array_sum($currentCounts);
        $previousTotal = array_sum($previousCounts);

        return [
            'total' => [
                'value' => $currentTotal,
                'change' => [
                    'value' => $this->percentChange($currentTotal, $previousTotal),
                    'label' => $label,
                ],
            ],
            'lead' => [
                'value' => $currentCounts[AccountStatus::Lead->value] ?? 0,
                'change' => [
                    'value' => $this->percentChange(
                        $currentCounts[AccountStatus::Lead->value] ?? 0,
                        $previousCounts[AccountStatus::Lead->value] ?? 0,
                    ),
                    'label' => $label,
                ],
            ],
            'opportunity' => [
                'value' => $currentCounts[AccountStatus::Opportunity->value] ?? 0,
                'change' => [
                    'value' => $this->percentChange(
                        $currentCounts[AccountStatus::Opportunity->value] ?? 0,
                        $previousCounts[AccountStatus::Opportunity->value] ?? 0,
                    ),
                    'label' => $label,
                ],
            ],
            'client' => [
                'value' => $currentCounts[AccountStatus::Client->value] ?? 0,
                'change' => [
                    'value' => $this->percentChange(
                        $currentCounts[AccountStatus::Client->value] ?? 0,
                        $previousCounts[AccountStatus::Client->value] ?? 0,
                    ),
                    'label' => $label,
                ],
            ],
            'archived' => [
                'value' => $currentCounts[AccountStatus::Archived->value] ?? 0,
                'change' => [
                    'value' => $this->percentChange(
                        $currentCounts[AccountStatus::Archived->value] ?? 0,
                        $previousCounts[AccountStatus::Archived->value] ?? 0,
                    ),
                    'label' => $label,
                ],
            ],
        ];
    }

    private function percentChange(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current === 0 ? 0 : 100;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    public function getAccountById(int $id): ?Account
    {
        return Account::with('contacts')->find($id);
    }

    public function bulkUpdateStatus(array $accountIds, AccountStatus $status): int
    {
        return Account::whereIn('id', $accountIds)->update(['status' => $status->value]);
    }

    public function bulkDelete(array $accountIds): int
    {
        return Account::whereIn('id', $accountIds)->delete();
    }
}

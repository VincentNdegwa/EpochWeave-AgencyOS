<?php

namespace App;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\ClientProfile;
use App\Models\PortalInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortalInvitationService
{
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

    public function validateInvitation(string $token): ?PortalInvitation
    {
        $invitation = PortalInvitation::with(['accountContact', 'account'])
            ->where('invitation_token', $token)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return null;
        }

        return $invitation;
    }

    public function completeSetup(string $token, string $password): ClientProfile
    {
        $invitation = $this->validateInvitation($token);

        if (!$invitation) {
            throw new \Exception('Invalid or expired invitation token.');
        }

        return DB::transaction(function () use ($invitation, $password) {
            $contact = $invitation->accountContact;

            $clientProfile = ClientProfile::firstOrCreate(
                ['email' => $contact->email],
                ['password' => bcrypt($password)]
            );

            $contact->update([
                'client_profile_id' => $clientProfile->id,
                'is_verified' => true,
            ]);

            $invitation->update(['is_used' => true]);

            return $clientProfile;
        });
    }

    public function getInvitationByToken(string $token): ?PortalInvitation
    {
        return PortalInvitation::with(['accountContact', 'account'])
            ->where('invitation_token', $token)
            ->first();
    }
}

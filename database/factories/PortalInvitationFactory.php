<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\PortalInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortalInvitation>
 */
class PortalInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'account_contact_id' => AccountContact::factory(),
            'invitation_token' => Str::random(64),
            'is_used' => false,
            'expires_at' => now()->addDays(7),
        ];
    }
}

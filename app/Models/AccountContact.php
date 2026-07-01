<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;

class AccountContact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'account_id',
        'client_profile_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'date_of_birth',
        'department',
        'preferred_contact_method',
        'notes',
        'is_verified',
        'is_primary',
        'receives_billing',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_primary' => 'boolean',
        'receives_billing' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }

    public function portalInvitation(): HasOne
    {
        return $this->hasOne(PortalInvitation::class, 'account_contact_id');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function socialProfiles(): MorphMany
    {
        return $this->morphMany(SocialProfile::class, 'profileable');
    }

    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'valueable');
    }
}

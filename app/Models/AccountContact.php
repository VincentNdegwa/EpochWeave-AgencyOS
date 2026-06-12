<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'is_verified',
        'is_primary',
        'receives_billing',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_primary' => 'boolean',
        'receives_billing' => 'boolean',
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
}

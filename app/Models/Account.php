<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($account) {
            // Delete related portal invitations when account is deleted (including soft delete)
            $account->portalInvitations()->delete();
        });
    }

    protected $fillable = [
        'workspace_id',
        'company_name',
        'website',
        'status',
        'token',
        'lifetime_value',
    ];

    protected $casts = [
        'status' => AccountStatus::class,
        'lifetime_value' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(AccountContact::class);
    }

    public function portalInvitations(): HasMany
    {
        return $this->hasMany(PortalInvitation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

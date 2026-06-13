<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'account_id',
        'account_contact_id',
        'created_by',
        'user_id',
        'template_id',
        'title',
        'proposal_number',
        'proposal_status_id',
        'valid_until',
        'content',
        'currency',
        'subtotal',
        'discount_total',
        'total_tax_amount',
        'grand_total',
        'requires_deposit',
        'deposit_type',
        'deposit_value',
        'deposit_amount',
        'token',
        'password_hash',
        'signer_name',
        'signer_email',
        'signer_company',
        'signature_data',
        'signed_ip',
        'signed_user_agent',
        'deposit_invoice_id',
        'project_id',
        'sent_at',
        'viewed_at',
        'last_viewed_at',
        'view_count',
        'decided_at',
        'accepted_at',
        'signed_at',
        'expired_at',
        'decline_reason',
    ];

    protected $casts = [
        'content' => 'array',
        'valid_until' => 'date',
        'deposit_value' => 'decimal:2',
        'quantity' => 'decimal:2',
        'requires_deposit' => 'boolean',
        'is_optional' => 'boolean',
        'is_selected' => 'boolean',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'decided_at' => 'datetime',
        'accepted_at' => 'datetime',
        'signed_at' => 'datetime',
        'expired_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total_tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountContact(): BelongsTo
    {
        return $this->belongsTo(AccountContact::class);
    }

    public function proposalStatus(): BelongsTo
    {
        return $this->belongsTo(ProposalStatus::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProposalTemplate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }
}

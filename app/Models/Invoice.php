<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'account_id',
        'account_contact_id',
        'proposal_id',
        'project_id',
        'created_by',
        'user_id',
        'invoice_number',
        'status',
        'token',
        'currency',
        'subtotal',
        'total_tax_amount',
        'discount_total',
        'grand_total',
        'amount_paid',
        'issue_date',
        'due_date',
        'notes',
        'sent_at',
        'paid_at',
        'voided_at',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
        'subtotal' => 'integer',
        'total_tax_amount' => 'integer',
        'discount_total' => 'integer',
        'grand_total' => 'integer',
        'amount_paid' => 'integer',
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

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}

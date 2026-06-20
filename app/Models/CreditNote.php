<?php

namespace App\Models;

use Database\Factories\CreditNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    /** @use HasFactory<CreditNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'invoice_id',
        'payment_id',
        'user_id',
        'amount',
        'reason',
        'refunded_at',
        'reference',
    ];

    protected $casts = [
        'amount' => 'integer',
        'refunded_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

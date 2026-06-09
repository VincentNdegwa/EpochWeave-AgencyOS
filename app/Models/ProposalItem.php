<?php

namespace App\Models;

use App\Enums\BillingFrequency;
use App\Enums\BillingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'product_id',
        'item_name',
        'description',
        'unit_label',
        'billing_type',
        'billing_frequency',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'is_optional',
        'is_selected',
        'position',
    ];

    protected $casts = [
        'billing_type' => BillingType::class,
        'billing_frequency' => BillingFrequency::class,
        'quantity' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_selected' => 'boolean',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

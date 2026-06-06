<?php

namespace App\Models;

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

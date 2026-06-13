<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\TaxType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'item_name',
        'description',
        'unit_label',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_type',
        'tax_value',
        'total_tax_amount',
        'total',
        'position',
    ];

    protected $casts = [
        'discount_type' => DiscountType::class,
        'tax_type' => TaxType::class,
        'quantity' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'tax_value' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

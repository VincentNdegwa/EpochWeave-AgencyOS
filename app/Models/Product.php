<?php

namespace App\Models;

use App\Enums\BillingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'unit_id',
        'name',
        'description',
        'sku',
        'unit_price',
        'billing_type',
        'is_active',
    ];

    protected $casts = [
        'billing_type' => BillingType::class,
        'unit_price' => 'integer',
        'is_active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}

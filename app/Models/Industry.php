<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'description',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public static function getDefaultRecords(): array
    {
        return [
            ['name' => 'Software & IT', 'color' => '#3b82f6', 'sort_order' => 0],
            ['name' => 'Financial Services', 'color' => '#10b981', 'sort_order' => 1],
            ['name' => 'Healthcare & Pharmaceuticals', 'color' => '#f59e0b', 'sort_order' => 2],
            ['name' => 'Manufacturing', 'color' => '#6366f1', 'sort_order' => 3],
            ['name' => 'Retail & E-commerce', 'color' => '#ec4899', 'sort_order' => 4],
            ['name' => 'Education', 'color' => '#8b5cf6', 'sort_order' => 5],
            ['name' => 'Real Estate', 'color' => '#14b8a6', 'sort_order' => 6],
            ['name' => 'Hospitality & Tourism', 'color' => '#f97316', 'sort_order' => 7],
            ['name' => 'Agriculture', 'color' => '#22c55e', 'sort_order' => 8],
            ['name' => 'Construction', 'color' => '#64748b', 'sort_order' => 9],
            ['name' => 'Energy & Utilities', 'color' => '#0ea5e9', 'sort_order' => 10],
            ['name' => 'Media & Entertainment', 'color' => '#a855f7', 'sort_order' => 11],
            ['name' => 'Government & Public Sector', 'color' => '#475569', 'sort_order' => 12],
            ['name' => 'Non-profit', 'color' => '#ef4444', 'sort_order' => 13],
            ['name' => 'Professional Services', 'color' => '#06b6d4', 'sort_order' => 14],
        ];
    }
}

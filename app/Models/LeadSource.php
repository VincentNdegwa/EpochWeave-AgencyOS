<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'description',
        'category',
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
            ['name' => 'Organic Search', 'color' => '#22c55e', 'category' => 'organic', 'sort_order' => 0],
            ['name' => 'Google Ads', 'color' => '#ef4444', 'category' => 'paid', 'sort_order' => 1],
            ['name' => 'Social Media (Organic)', 'color' => '#3b82f6', 'category' => 'organic', 'sort_order' => 2],
            ['name' => 'Social Media (Paid)', 'color' => '#8b5cf6', 'category' => 'paid', 'sort_order' => 3],
            ['name' => 'LinkedIn Outreach', 'color' => '#0ea5e9', 'category' => 'outbound', 'sort_order' => 4],
            ['name' => 'Email Campaign', 'color' => '#f59e0b', 'category' => 'outbound', 'sort_order' => 5],
            ['name' => 'Referral — Customer', 'color' => '#10b981', 'category' => 'referral', 'sort_order' => 6],
            ['name' => 'Referral — Partner', 'color' => '#14b8a6', 'category' => 'referral', 'sort_order' => 7],
            ['name' => 'Webinar / Event', 'color' => '#ec4899', 'category' => 'event', 'sort_order' => 8],
            ['name' => 'Cold Call', 'color' => '#64748b', 'category' => 'outbound', 'sort_order' => 9],
            ['name' => 'Trade Show', 'color' => '#f97316', 'category' => 'event', 'sort_order' => 10],
            ['name' => 'Content Download', 'color' => '#6366f1', 'category' => 'organic', 'sort_order' => 11],
            ['name' => 'Direct Traffic', 'color' => '#475569', 'category' => 'organic', 'sort_order' => 12],
            ['name' => 'Other', 'color' => '#94a3b8', 'category' => null, 'sort_order' => 13],
        ];
    }
}

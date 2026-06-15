<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'title',
        'color',
        'is_system',
        'automation_trigger',
        'position',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'position' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function getDefaultStatuses(): array
    {
        return [
            ['title' => 'Draft', 'color' => '#64748b', 'is_system' => true, 'automation_trigger' => 'draft', 'position' => 0],
            ['title' => 'Sent', 'color' => '#3b82f6', 'is_system' => true, 'automation_trigger' => 'sent', 'position' => 1],
            ['title' => 'Overdue', 'color' => '#f97316', 'is_system' => true, 'automation_trigger' => 'overdue', 'position' => 2],
            ['title' => 'Paid', 'color' => '#22c55e', 'is_system' => true, 'automation_trigger' => 'paid', 'position' => 3],
            ['title' => 'Voided', 'color' => '#ef4444', 'is_system' => true, 'automation_trigger' => 'voided', 'position' => 4],
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalStatus extends Model
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

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public static function getDefaultStatuses(): array
    {
        return [
            ['title' => 'Draft', 'color' => '#64748b', 'is_system' => true, 'automation_trigger' => 'draft', 'position' => 0],
            ['title' => 'Sent', 'color' => '#3b82f6', 'is_system' => true, 'automation_trigger' => 'sent', 'position' => 1],
            ['title' => 'Accepted', 'color' => '#22c55e', 'is_system' => true, 'automation_trigger' => 'accepted', 'position' => 2],
            ['title' => 'Declined', 'color' => '#ef4444', 'is_system' => true, 'automation_trigger' => 'declined', 'position' => 3],
            ['title' => 'Expired', 'color' => '#f59e0b', 'is_system' => true, 'automation_trigger' => 'expired', 'position' => 4],
        ];
    }
}

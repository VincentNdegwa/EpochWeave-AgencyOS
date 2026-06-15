<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStatus extends Model
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public static function getDefaultStatuses(): array
    {
        return [
            ['title' => 'Planning', 'color' => '#64748b', 'is_system' => true, 'automation_trigger' => 'planning', 'position' => 0],
            ['title' => 'Active', 'color' => '#3b82f6', 'is_system' => true, 'automation_trigger' => 'active', 'position' => 1],
            ['title' => 'On Hold', 'color' => '#f59e0b', 'is_system' => true, 'automation_trigger' => 'paused', 'position' => 2],
            ['title' => 'Completed', 'color' => '#22c55e', 'is_system' => true, 'automation_trigger' => 'completed', 'position' => 3],
            ['title' => 'Cancelled', 'color' => '#ef4444', 'is_system' => true, 'automation_trigger' => 'cancelled', 'position' => 4],
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected function isClosed(): Attribute
    {
        return Attribute::make(
            get: fn () => in_array($this->automation_trigger, ['completed', 'cancelled']),
        );
    }

    public static function getDefaultStatuses(): array
    {
        return [
            ['title' => 'Backlog', 'color' => '#94a3b8', 'is_system' => true, 'automation_trigger' => 'backlog', 'position' => 0],
            ['title' => 'Todo', 'color' => '#64748b', 'is_system' => true, 'automation_trigger' => 'unstarted', 'position' => 1],
            ['title' => 'In Progress', 'color' => '#3b82f6', 'is_system' => true, 'automation_trigger' => 'active', 'position' => 2],
            ['title' => 'In Review', 'color' => '#a855f7', 'is_system' => true, 'automation_trigger' => 'review', 'position' => 3],
            ['title' => 'Completed', 'color' => '#22c55e', 'is_system' => true, 'automation_trigger' => 'completed', 'position' => 4],
            ['title' => 'Cancelled', 'color' => '#ef4444', 'is_system' => true, 'automation_trigger' => 'cancelled', 'position' => 5],
        ];
    }
}

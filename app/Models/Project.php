<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'account_id',
        'name',
        'description',
        'color',
        'status',
        'hourly_rate',
        'currency',
        'start_date',
        'due_date',
        'tasks_total',
        'tasks_completed',
        'hours_logged',
        'hours_budgeted',
        'portal_visible',
        'completed_at',
        'archived_at',
    ];

    protected $casts = [
        'hourly_rate' => 'integer',
        'start_date' => 'date',
        'due_date' => 'date',
        'tasks_total' => 'integer',
        'tasks_completed' => 'integer',
        'hours_logged' => 'decimal:2',
        'hours_budgeted' => 'decimal:2',
        'portal_visible' => 'boolean',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'hourly_rate', 'joined_at'])
            ->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

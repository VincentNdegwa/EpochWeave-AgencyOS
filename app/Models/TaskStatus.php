<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'position',
        'is_default',
        'is_closed',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_default' => 'boolean',
        'is_closed' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

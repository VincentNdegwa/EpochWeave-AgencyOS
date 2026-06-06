<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'thumbnail_url',
        'content',
        'is_default',
    ];

    protected $casts = [
        'content' => 'array',
        'is_default' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}

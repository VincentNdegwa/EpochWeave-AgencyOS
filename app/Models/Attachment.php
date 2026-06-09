<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

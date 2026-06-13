<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSetting extends Model
{
    use HasFactory;

    public const SUBMODULE_PROPOSALS = 'proposals';
    public const SUBMODULE_NOTIFICATIONS = 'notifications';
    public const SUBMODULE_AUTOMATION = 'automation';

    protected $fillable = [
        'workspace_id',
        'submodule',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}

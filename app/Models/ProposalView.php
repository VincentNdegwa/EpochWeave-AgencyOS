<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProposalView extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'viewer_id',
        'viewer_type',
        'viewed_at',
        'ip_address',
        'user_agent',
        'is_revisit',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'is_revisit' => 'boolean',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function viewer(): MorphTo
    {
        return $this->morphTo();
    }
}

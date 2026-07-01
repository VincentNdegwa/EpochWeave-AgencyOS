<?php

namespace App\Models;

use App\Enums\EngagementDirection;
use App\Enums\EngagementOutcome;
use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Engagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'account_id',
        'user_id',
        'type',
        'direction',
        'status',
        'subject',
        'content',
        'proposal_id',
        'invoice_id',
        'project_id',
        'scheduled_at',
        'completed_at',
        'follow_up_at',
        'outcome',
    ];

    protected $casts = [
        'type' => EngagementType::class,
        'direction' => EngagementDirection::class,
        'status' => EngagementStatus::class,
        'outcome' => EngagementOutcome::class,
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'follow_up_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

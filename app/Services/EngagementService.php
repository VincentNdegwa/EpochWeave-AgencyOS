<?php

namespace App\Services;

use App\Models\Engagement;

class EngagementService
{
    public function createEngagement(int $workspaceId, array $data): Engagement
    {
        return Engagement::create([
            'workspace_id' => $workspaceId,
            'account_id' => $data['account_id'],
            'user_id' => $data['user_id'] ?? null,
            'type' => $data['type'],
            'direction' => $data['direction'],
            'status' => $data['status'],
            'subject' => $data['subject'] ?? null,
            'content' => $data['content'] ?? null,
            'proposal_id' => $data['proposal_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
            'outcome' => $data['outcome'] ?? null,
        ]);
    }

    public function updateEngagement(Engagement $engagement, array $data): Engagement
    {
        $engagement->update([
            'user_id' => $data['user_id'] ?? $engagement->user_id,
            'type' => $data['type'] ?? $engagement->type,
            'direction' => $data['direction'] ?? $engagement->direction,
            'status' => $data['status'] ?? $engagement->status,
            'subject' => $data['subject'] ?? $engagement->subject,
            'content' => $data['content'] ?? $engagement->content,
            'proposal_id' => $data['proposal_id'] ?? $engagement->proposal_id,
            'invoice_id' => $data['invoice_id'] ?? $engagement->invoice_id,
            'project_id' => $data['project_id'] ?? $engagement->project_id,
            'scheduled_at' => $data['scheduled_at'] ?? $engagement->scheduled_at,
            'completed_at' => $data['completed_at'] ?? $engagement->completed_at,
            'follow_up_at' => $data['follow_up_at'] ?? $engagement->follow_up_at,
            'outcome' => $data['outcome'] ?? $engagement->outcome,
        ]);

        return $engagement;
    }

    public function deleteEngagement(Engagement $engagement): void
    {
        $engagement->delete();
    }
}

<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalStatus;

class MoveService
{
    public function __construct(
        private MovementRulesService $movementRulesService
    ) {}

    public function moveProposal(Proposal $proposal, int $targetStatusId): array
    {
        $proposalStatuses = ProposalStatus::where('workspace_id', $proposal->workspace_id)
            ->get()
            ->toArray();

        $targetStatus = collect($proposalStatuses)
            ->firstWhere('id', $targetStatusId);

        if (!$targetStatus) {
            return [
                'success' => false,
                'error' => 'Target status not found.',
            ];
        }

        $currentTrigger = $proposal->proposalStatus?->automation_trigger;
        $targetTrigger = $targetStatus->automation_trigger;

        $validation = $this->movementRulesService->canMove($currentTrigger, $targetTrigger, $proposalStatuses);
        if (!$validation['allowed']) {
            return [
                'success' => false,
                'error' => $validation['error'],
            ];
        }

        $proposal->proposal_status_id = $targetStatus->id;

        if ($targetTrigger === 'sent' && !$proposal->sent_at) {
            $proposal->sent_at = now();
        }

        if ($targetTrigger === 'accepted' && !$proposal->accepted_at) {
            $proposal->accepted_at = now();
        }

        $proposal->save();

        return [
            'success' => true,
            'proposal' => $proposal,
            'target_status' => $targetStatus,
        ];
    }
}

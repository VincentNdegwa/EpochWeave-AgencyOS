<?php

namespace App\Services;

use App\Actions\SendProposal;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use Illuminate\Support\Facades\DB;

class MoveService
{
    public function __construct(
        private MovementRulesService $movementRulesService,
        private SendProposal $sendProposal
    ) {}

    public function moveProposal(Proposal $proposal, int $targetStatusId): array
    {
        return DB::transaction(function () use ($proposal, $targetStatusId) {
            $proposalStatuses = ProposalStatus::where('workspace_id', $proposal->workspace_id)
                ->get();

            $targetStatus = $proposalStatuses
                ->firstWhere('id', $targetStatusId);

            if (!$targetStatus) {
                return [
                    'success' => false,
                    'error' => 'Target status not found.',
                ];
            }

            $currentTrigger = $proposal->proposalStatus?->automation_trigger;
            $targetTrigger = $targetStatus->automation_trigger;

            $validation = $this->movementRulesService->canMove($currentTrigger, $targetTrigger, $proposalStatuses->toArray());
            if (!$validation['allowed']) {
                return [
                    'success' => false,
                    'error' => $validation['error'],
                ];
            }

            $proposal->proposal_status_id = $targetStatus->id;

            if ($targetTrigger === 'sent' && !$proposal->sent_at) {
                $this->sendProposal->send($proposal);
            } elseif ($targetTrigger === 'accepted' && !$proposal->accepted_at) {
                $proposal->accepted_at = now();
                $proposal->save();
            } else {
                $proposal->save();
            }

            return [
                'success' => true,
                'proposal' => $proposal,
                'target_status' => $targetStatus,
            ];
        });
    }
}

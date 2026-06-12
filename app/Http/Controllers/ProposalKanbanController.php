<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Services\MovementRulesService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalKanbanController extends Controller
{
    public function __construct(
        private MovementRulesService $movementRulesService
    ) {}

    public function move(Request $request, Proposal $proposal)
    {
        $request->validate([
            'target_status_id' => 'required|exists:proposal_statuses,id'
        ]);

        $targetStatus = ProposalStatus::where('workspace_id', $proposal->workspace_id)
            ->findOrFail($request->target_status_id);

        $currentTrigger = $proposal->proposalStatus?->automation_trigger;
        $targetTrigger = $targetStatus->automation_trigger;

        $validation = $this->movementRulesService->canMove($currentTrigger, $targetTrigger);
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['error']]);
        }

        $proposal->proposal_status_id = $targetStatus->id;

        if ($targetTrigger === 'sent' && !$proposal->sent_at) {
            $proposal->sent_at = now();
        }

        if ($targetTrigger === 'accepted' && !$proposal->accepted_at) {
            $proposal->accepted_at = now();
        }

        $proposal->save();

        return back();
    }
}

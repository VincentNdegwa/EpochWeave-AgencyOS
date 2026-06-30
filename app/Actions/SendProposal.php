<?php

namespace App\Actions;

use App\Enums\AccountStatus;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Notifications\ProposalSent;
use App\Services\ActivityService;

class SendProposal
{
    public function __construct(private ActivityService $activityService) {}

    public function send(Proposal $proposal): Proposal
    {
        $proposal->loadMissing('accountContact');
        if (! $proposal->accountContact) {
            throw new \InvalidArgumentException('Proposal must have an assigned contact to send.');
        }

        if (! $proposal->token) {
            throw new \InvalidArgumentException('Proposal must have a token to generate public link.');
        }

        $sentStatus = ProposalStatus::where('workspace_id', $proposal->workspace_id)
            ->where('automation_trigger', 'sent')
            ->first();

        $data = ['sent_at' => now()];
        if ($sentStatus) {
            $data['proposal_status_id'] = $sentStatus->id;
        }

        $proposal->update($data);

        if ($proposal->account_id) {
            $account = $proposal->account;

            if ($account->status === AccountStatus::Lead) {
                $account->update(['status' => AccountStatus::Opportunity->value]);
            }
        }

        $this->activityService->sent($proposal);
        $proposal->accountContact->notify(new ProposalSent($proposal));

        return $proposal->fresh();
    }
}

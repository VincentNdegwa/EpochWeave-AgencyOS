<?php

namespace App\Actions;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Notifications\ProposalSent;

class SendProposal
{
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
        $proposal->accountContact->notify(new ProposalSent($proposal));

        return $proposal->fresh();
    }
}

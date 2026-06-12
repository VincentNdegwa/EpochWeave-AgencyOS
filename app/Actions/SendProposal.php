<?php

namespace App\Actions;

use App\Models\Proposal;
use App\Notifications\ProposalSent;

class SendProposal
{
    public function send(Proposal $proposal): Proposal
    {
        $proposal->loadMissing("accountContact");
        if (!$proposal->accountContact) {
            throw new \InvalidArgumentException('Proposal must have an assigned contact to send.');
        }

        if (!$proposal->token) {
            throw new \InvalidArgumentException('Proposal must have a token to generate public link.');
        }

        $proposal->update(['sent_at' => now()]);
        $proposal->accountContact->notify(new ProposalSent($proposal));

        return $proposal->fresh();
    }
}

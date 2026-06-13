<?php

namespace App\Jobs;

use App\Models\Proposal;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateInvoiceFromProposal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal
    ) {}

    public function handle(InvoiceService $invoiceService): void
    {
        try {
            $project = $this->proposal->project;

            $invoiceService->createInvoiceFromProposal($this->proposal, $project);

            Log::info('Invoice created from proposal', [
                'proposal_id' => $this->proposal->id,
                'workspace_id' => $this->proposal->workspace_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from proposal', [
                'proposal_id' => $this->proposal->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

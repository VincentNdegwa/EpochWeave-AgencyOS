<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\ProposalService;
use App\Models\Proposal;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(ProposalService::class);
    $proposal = Proposal::find(2);
    
    if (!$proposal) {
        echo "Proposal not found\n";
        exit;
    }
    
    echo "Before calculation:\n";
    echo " - Subtotal: " . $proposal->subtotal . "\n";
    echo " - Discount Total: " . $proposal->discount_total . "\n";
    echo " - Tax Amount: " . $proposal->tax_amount . "\n";
    echo " - Grand Total: " . $proposal->grand_total . "\n";
    
    // Calculate totals
    $totals = $service->calculateTotalsFromContent($proposal);
    
    echo "\nCalculated totals:\n";
    echo " - Subtotal: " . $totals['subtotal'] . "\n";
    echo " - Discount Total: " . $totals['discount_total'] . "\n";
    echo " - Tax Amount: " . $totals['tax_amount'] . "\n";
    echo " - Grand Total: " . $totals['grand_total'] . "\n";
    
    // Update the proposal
    $updatedProposal = $service->updateProposalTotals($proposal);
    
    echo "\nAfter update:\n";
    echo " - Subtotal: " . $updatedProposal->subtotal . "\n";
    echo " - Discount Total: " . $updatedProposal->discount_total . "\n";
    echo " - Tax Amount: " . $updatedProposal->tax_amount . "\n";
    echo " - Grand Total: " . $updatedProposal->grand_total . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

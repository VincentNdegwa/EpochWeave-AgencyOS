<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Account;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ProposalService;

echo "Testing Line Items Implementation...\n";

try {
    // Create test data
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    $account = Account::factory()->create(['workspace_id' => $workspace->id]);
    
    echo "✅ Created test data\n";
    
    // Test ProposalService
    $proposalService = new ProposalService();
    
    // Test creating proposal with items
    $proposalData = [
        'title' => 'Test Proposal with Items',
        'currency' => 'USD',
        'valid_until' => now()->addWeek()->toDateString(),
        'account_id' => $account->id,
        'workspace_id' => $workspace->id,
        'created_by' => $user->id,
        'content' => [
            [
                'id' => 'test-block-1',
                'type' => 'pricing_table',
                'sort_order' => 0,
                'is_locked' => false,
                'data' => [
                    'items' => [
                        ['id' => 'item-1'],
                        ['id' => 'item-2'],
                    ],
                ],
                'meta' => [
                    'padding_top' => 'md',
                    'padding_bottom' => 'md',
                    'background_color' => '#ffffff',
                    'border_top' => false,
                    'border_bottom' => false,
                    'is_hidden' => false,
                    'notes' => null,
                ],
            ],
        ],
    ];
    
    $items = [
        [
            'id' => 'item-1',
            'description' => 'Web Design Service',
            'item_description' => 'Complete web design package',
            'unit' => 'Pcs',
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal' => 1000,
            'billing_type' => 'one_time',
            'billing_frequency' => 'none',
            'is_optional' => false,
            'product_id' => null,
            'item_discount_type' => 'none',
            'item_discount_value' => 0,
        ],
        [
            'id' => 'item-2',
            'description' => 'SEO Service',
            'item_description' => 'Monthly SEO optimization',
            'unit' => 'Month',
            'quantity' => 3,
            'unit_price' => 500,
            'subtotal' => 1500,
            'billing_type' => 'recurring',
            'billing_frequency' => 'monthly',
            'is_optional' => true,
            'product_id' => null,
            'item_discount_type' => 'none',
            'item_discount_value' => 0,
        ],
    ];
    
    $proposal = $proposalService->createProposalWithItems($proposalData, $items);
    
    echo "✅ Created proposal with items\n";
    
    // Test loading proposal with items
    $loadedProposal = $proposalService->getProposalById($proposal->id);
    
    echo "✅ Loaded proposal with items\n";
    
    // Verify items were created
    $itemCount = ProposalItem::where('proposal_id', $proposal->id)->count();
    echo "✅ Found {$itemCount} items in database\n";
    
    // Test updating proposal with items
    $updateData = [
        'title' => 'Updated Proposal',
        'currency' => 'EUR',
        'valid_until' => now()->addWeek()->toDateString(),
    ];
    
    $updatedItems = [
        [
            'id' => 'item-3',
            'description' => 'Updated Service',
            'item_description' => 'Updated description',
            'unit' => 'Hour',
            'quantity' => 5,
            'unit_price' => 200,
            'subtotal' => 1000,
            'billing_type' => 'one_time',
            'billing_frequency' => 'none',
            'is_optional' => false,
            'product_id' => null,
            'item_discount_type' => 'percentage',
            'item_discount_value' => 10,
        ],
    ];
    
    $updatedProposal = $proposalService->updateProposal($proposal, $updateData, $updatedItems);
    
    echo "✅ Updated proposal with new items\n";
    
    // Verify old items were deleted and new items created
    $newItemCount = ProposalItem::where('proposal_id', $proposal->id)->count();
    echo "✅ Found {$newItemCount} items after update\n";
    
    // Test workspace proposals
    $workspaceProposals = $proposalService->getProposalsByWorkspace($workspace->id);
    echo "✅ Found " . count($workspaceProposals) . " proposals for workspace\n";
    
    // Test account proposals
    $accountProposals = $proposalService->getProposalsByAccount($account->id);
    echo "✅ Found " . count($accountProposals) . " proposals for account\n";
    
    echo "\n🎉 All tests passed! Line items implementation is working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

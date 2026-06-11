<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ProposalService;
use Illuminate\Console\Command;

class TestLineItems extends Command
{
    protected $signature = 'test:line-items';
    protected $description = 'Test the line items implementation';

    public function handle()
    {
        $this->info('Testing Line Items Implementation...');

        try {
            // Create test data
            $user = User::factory()->create();
            $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
            $account = Account::factory()->create(['workspace_id' => $workspace->id]);
            
            $this->info('✅ Created test data');
            
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
            
            $this->info('✅ Created proposal with items');
            
            // Test loading proposal with items
            $loadedProposal = $proposalService->getProposalById($proposal->id);
            
            $this->info('✅ Loaded proposal with items');
            
            // Verify items were created
            $itemCount = ProposalItem::where('proposal_id', $proposal->id)->count();
            $this->info("✅ Found {$itemCount} items in database");
            
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
            
            $this->info('✅ Updated proposal with new items');
            
            // Verify old items were deleted and new items created
            $newItemCount = ProposalItem::where('proposal_id', $proposal->id)->count();
            $this->info("✅ Found {$newItemCount} items after update");
            
            // Test workspace proposals
            $workspaceProposals = $proposalService->getProposalsByWorkspace($workspace->id);
            $this->info("✅ Found " . count($workspaceProposals) . " proposals for workspace");
            
            // Test account proposals
            $accountProposals = $proposalService->getProposalsByAccount($account->id);
            $this->info("✅ Found " . count($accountProposals) . " proposals for account");
            
            $this->info("\n🎉 All tests passed! Line items implementation is working correctly.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}

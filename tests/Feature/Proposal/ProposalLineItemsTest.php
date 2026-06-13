<?php

namespace Tests\Feature\Proposal;

use App\Models\Account;
use App\Models\Product;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ProposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProposalLineItemsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->account = Account::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $role = Role::create(['name' => 'admin']);
        $this->user->addRole($role, $this->workspace);

        $this->actingAs($this->user)
            ->withSession(['current_workspace_id' => $this->workspace->id]);
    }

    public function test_it_can_create_proposal_with_line_items(): void
    {
        $proposalData = [
            'title' => 'Test Proposal with Items',
            'currency' => 'USD',
            'valid_until' => now()->addWeek()->toDateString(),
            'account_id' => $this->account->id,
            'blocks' => [
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
            'line_items' => [
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
            ],
        ];

        $response = $this->post(route('proposals.store'), $proposalData);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposals', [
            'title' => 'Test Proposal with Items',
            'currency' => $this->workspace->currency,
            'account_id' => $this->account->id,
        ]);

        $proposal = Proposal::where('title', 'Test Proposal with Items')->first();
        $this->assertCount(2, $proposal->items);

        $this->assertDatabaseHas('proposal_items', [
            'proposal_id' => $proposal->id,
            'item_name' => 'Web Design Service',
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
            'billing_type' => 'one_time',
            'is_optional' => false,
        ]);

        $this->assertDatabaseHas('proposal_items', [
            'proposal_id' => $proposal->id,
            'item_name' => 'SEO Service',
            'unit_price' => 500,
            'quantity' => 3,
            'subtotal' => 1500,
            'billing_type' => 'recurring',
            'is_optional' => true,
        ]);
    }

    public function test_it_can_update_proposal_with_line_items(): void
    {
        // Create initial proposal
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        // Create initial items
        ProposalItem::factory()->create([
            'proposal_id' => $proposal->id,
            'item_name' => 'Old Service',
            'unit_price' => 100,
            'quantity' => 1,
            'subtotal' => 100,
        ]);

        $updateData = [
            'title' => 'Updated Proposal',
            'currency' => 'EUR',
            'valid_until' => now()->addWeek()->toDateString(),
            'blocks' => [
                [
                    'id' => 'test-block-1',
                    'type' => 'pricing_table',
                    'sort_order' => 0,
                    'is_locked' => false,
                    'data' => [
                        'items' => [
                            ['id' => 'new-item-1'],
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
            'line_items' => [
                [
                    'id' => 'new-item-1',
                    'description' => 'Updated Service',
                    'item_description' => 'Updated description',
                    'unit' => 'Hour',
                    'quantity' => 5,
                    'unit_price' => 200,
                    'subtotal' => 900,
                    'billing_type' => 'one_time',
                    'billing_frequency' => 'none',
                    'is_optional' => false,
                    'product_id' => null,
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'tax_type' => 'none',
                    'tax_value' => 0,
                ],
            ],
        ];

        $response = $this->put(route('proposals.update', $proposal->id), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'title' => 'Updated Proposal',
            'currency' => 'EUR',
        ]);

        // Old item should be deleted
        $this->assertDatabaseMissing('proposal_items', [
            'proposal_id' => $proposal->id,
            'item_name' => 'Old Service',
        ]);

        // New item should exist
        $this->assertDatabaseHas('proposal_items', [
            'proposal_id' => $proposal->id,
            'item_name' => 'Updated Service',
            'unit_price' => 200,
            'quantity' => 5,
            'subtotal' => 1000,
            'billing_type' => 'one_time',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        $proposal->refresh();
        $this->assertCount(1, $proposal->items);
        $this->assertEquals(1000, (int) $proposal->subtotal);
        $this->assertEquals(100, (int) $proposal->discount_total);
        $this->assertEquals(0.0, (float) $proposal->total_tax_amount);
        $this->assertEquals(900, (int) $proposal->grand_total);
    }

    public function test_it_can_load_proposal_with_items_and_avoid_n_plus_one(): void
    {
        // Create proposal with multiple items
        $proposal = Proposal::factory()->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        // Create multiple items
        $items = ProposalItem::factory()->count(5)->create([
            'proposal_id' => $proposal->id,
        ]);

        // Track queries to ensure no N+1
        DB::enableQueryLog();

        $response = $this->get(route('proposals.show', $proposal->id));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should only have a few queries (not N+1)
        $this->assertLessThan(10, count($queries), 'Too many queries detected - possible N+1 problem');

        $response->assertOk();

        // Verify items are loaded
        $proposal->refresh();
        $this->assertCount(5, $proposal->items);
    }

    public function test_it_can_create_proposal_without_line_items(): void
    {
        $proposalData = [
            'title' => 'Simple Proposal',
            'currency' => 'USD',
            'valid_until' => now()->addWeek()->toDateString(),
            'account_id' => $this->account->id,
            'blocks' => [
                [
                    'id' => 'test-block-1',
                    'type' => 'rich_text',
                    'sort_order' => 0,
                    'is_locked' => false,
                    'data' => [
                        'content' => '<p>Simple content</p>',
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

        $response = $this->post(route('proposals.store'), $proposalData);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposals', [
            'title' => 'Simple Proposal',
            'currency' => $this->workspace->currency,
        ]);

        $proposal = Proposal::where('title', 'Simple Proposal')->first();
        $this->assertCount(0, $proposal->items);
    }

    public function test_it_validates_line_items_data(): void
    {
        $invalidData = [
            'title' => 'Test Proposal',
            'currency' => 'USD',
            'valid_until' => now()->addWeek()->toDateString(),
            'account_id' => $this->account->id,
            'blocks' => [
                [
                    'id' => 'test-block-1',
                    'type' => 'pricing_table',
                    'sort_order' => 0,
                    'is_locked' => false,
                    'data' => ['items' => []],
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
            'line_items' => [
                [
                    'id' => 'item-1',
                    'description' => '', // Missing required description
                    'unit' => 'Pcs',
                    'quantity' => -1, // Invalid quantity
                    'unit_price' => -100, // Invalid price
                    'subtotal' => 0,
                    'billing_type' => 'invalid_type', // Invalid billing type
                    'billing_frequency' => 'invalid_freq', // Invalid frequency
                    'is_optional' => 'not_boolean', // Invalid boolean
                ],
            ],
        ];

        $response = $this->post(route('proposals.store'), $invalidData);

        $response->assertSessionHasErrors([
            'line_items.0.description',
            'line_items.0.quantity',
            'line_items.0.unit_price',
            'line_items.0.billing_type',
            'line_items.0.billing_frequency',
            'line_items.0.is_optional',
        ]);
    }

    public function test_it_handles_empty_line_items_array(): void
    {
        $proposalData = [
            'title' => 'Proposal with Empty Items',
            'currency' => 'USD',
            'valid_until' => now()->addWeek()->toDateString(),
            'account_id' => $this->account->id,
            'blocks' => [
                [
                    'id' => 'test-block-1',
                    'type' => 'pricing_table',
                    'sort_order' => 0,
                    'is_locked' => false,
                    'data' => ['items' => []],
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
            'line_items' => [],
        ];

        $response = $this->post(route('proposals.store'), $proposalData);

        $response->assertRedirect();
        $this->assertDatabaseHas('proposals', [
            'title' => 'Proposal with Empty Items',
        ]);

        $proposal = Proposal::where('title', 'Proposal with Empty Items')->first();
        $this->assertCount(0, $proposal->items);
    }

    public function test_it_can_get_proposals_by_workspace_with_items(): void
    {
        // Create multiple proposals with items
        $proposals = Proposal::factory()->count(3)->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        foreach ($proposals as $proposal) {
            ProposalItem::factory()->count(2)->create(['proposal_id' => $proposal->id]);
        }

        // Track queries to ensure no N+1
        DB::enableQueryLog();

        $proposals = app(ProposalService::class)
            ->getProposalsByWorkspace($this->workspace->id);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should only have a few queries (not N+1)
        $this->assertLessThan(5, count($queries), 'Too many queries detected in getProposalsByWorkspace');

        $this->assertCount(3, $proposals);
        foreach ($proposals as $proposal) {
            $this->assertTrue($proposal->relationLoaded('items'));
            $this->assertCount(2, $proposal->items);
        }
    }

    public function test_it_can_get_proposals_by_account_with_items(): void
    {
        // Create proposals with items
        $proposals = Proposal::factory()->count(2)->create([
            'workspace_id' => $this->workspace->id,
            'account_id' => $this->account->id,
        ]);

        foreach ($proposals as $proposal) {
            ProposalItem::factory()->count(3)->create(['proposal_id' => $proposal->id]);
        }

        // Track queries to ensure no N+1
        DB::enableQueryLog();

        $proposals = app(ProposalService::class)
            ->getProposalsByAccount($this->account->id);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should only have a few queries (not N+1)
        $this->assertLessThan(5, count($queries), 'Too many queries detected in getProposalsByAccount');

        $this->assertCount(2, $proposals);
        foreach ($proposals as $proposal) {
            $this->assertTrue($proposal->relationLoaded('items'));
            $this->assertCount(3, $proposal->items);
        }
    }

    public function test_it_handles_product_relationships_in_items(): void
    {
        // Create a product
        $product = Product::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $proposalData = [
            'title' => 'Proposal with Product Items',
            'currency' => 'USD',
            'valid_until' => now()->addWeek()->toDateString(),
            'account_id' => $this->account->id,
            'blocks' => [
                [
                    'id' => 'test-block-1',
                    'type' => 'pricing_table',
                    'sort_order' => 0,
                    'is_locked' => false,
                    'data' => [
                        'items' => [
                            ['id' => 'item-1'],
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
            'line_items' => [
                [
                    'id' => 'item-1',
                    'description' => 'Product Service',
                    'item_description' => 'Service linked to product',
                    'unit' => 'Pcs',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'subtotal' => 1000,
                    'billing_type' => 'one_time',
                    'billing_frequency' => 'none',
                    'is_optional' => false,
                    'product_id' => $product->id,
                    'item_discount_type' => 'none',
                    'item_discount_value' => 0,
                ],
            ],
        ];

        $response = $this->post(route('proposals.store'), $proposalData);

        $response->assertRedirect();
        $proposal = Proposal::where('title', 'Proposal with Product Items')->first();

        $this->assertDatabaseHas('proposal_items', [
            'proposal_id' => $proposal->id,
            'product_id' => $product->id,
            'item_name' => 'Product Service',
        ]);

        // Test loading with product relationship
        $loadedProposal = app(ProposalService::class)->getProposalById($proposal->id);
        $this->assertTrue($loadedProposal->relationLoaded('items'));

        $item = $loadedProposal->items->first();
        $this->assertTrue($item->relationLoaded('product'));
        $this->assertEquals($product->id, $item->product->id);
    }
}

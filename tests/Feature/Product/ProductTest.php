<?php

namespace Tests\Feature\Product;

use App\Enums\BillingType;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Workspace;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService;
    }

    public function test_can_create_product(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $data = [
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
            'name' => 'Web Development',
            'description' => 'Custom web development services',
            'sku' => 'WEB-001',
            'unit_price' => 5000,
            'billing_type' => BillingType::OneTime->value,
            'is_active' => true,
        ];

        $product = $this->productService->createProduct($data);

        $this->assertDatabaseHas('products', [
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
            'name' => 'Web Development',
            'sku' => 'WEB-001',
            'unit_price' => 5000,
            'billing_type' => BillingType::OneTime->value,
        ]);
        $this->assertEquals('Web Development', $product->name);
        $this->assertEquals(5000, $product->unit_price);
    }

    public function test_can_update_product(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);
        $data = [
            'name' => 'Updated Product Name',
            'unit_price' => 7500,
            'billing_type' => BillingType::Recurring->value,
        ];

        $updatedProduct = $this->productService->updateProduct($product, $data);

        $this->assertEquals('Updated Product Name', $updatedProduct->name);
        $this->assertEquals(7500, $updatedProduct->unit_price);
        $this->assertEquals(BillingType::Recurring, $updatedProduct->billing_type);
    }

    public function test_can_delete_product(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);

        $this->productService->deleteProduct($product);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_can_get_products_by_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $otherProductUnit = ProductUnit::factory()->create(['workspace_id' => $otherWorkspace->id]);

        Product::factory()->count(3)->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);
        Product::factory()->count(2)->create([
            'workspace_id' => $otherWorkspace->id,
            'unit_id' => $otherProductUnit->id,
        ]);

        $products = $this->productService->getProductsByWorkspace($workspace->id);

        $this->assertCount(3, $products);
    }

    public function test_can_get_active_products_by_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);

        Product::factory()->count(3)->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
            'is_active' => true,
        ]);
        Product::factory()->count(2)->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
            'is_active' => false,
        ]);

        $activeProducts = $this->productService->getActiveProductsByWorkspace($workspace->id);

        $this->assertCount(3, $activeProducts);
    }

    public function test_can_get_product_by_id(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);

        $foundProduct = $this->productService->getProductById($product->id);

        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id, $foundProduct->id);
    }

    public function test_product_belongs_to_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);

        $this->assertEquals($workspace->id, $product->workspace->id);
    }

    public function test_product_belongs_to_unit(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
        ]);

        $this->assertEquals($productUnit->id, $product->unit->id);
    }

    public function test_product_billing_type_is_cast_to_enum(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $product = Product::factory()->create([
            'workspace_id' => $workspace->id,
            'unit_id' => $productUnit->id,
            'billing_type' => BillingType::Recurring->value,
        ]);

        $this->assertInstanceOf(BillingType::class, $product->billing_type);
        $this->assertEquals(BillingType::Recurring, $product->billing_type);
    }
}

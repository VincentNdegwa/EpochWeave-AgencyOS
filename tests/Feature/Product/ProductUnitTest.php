<?php

namespace Tests\Feature\Product;

use App\Models\ProductUnit;
use App\Models\Workspace;
use App\Services\ProductUnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;

    private ProductUnitService $productUnitService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productUnitService = new ProductUnitService;
    }

    public function test_can_create_product_unit(): void
    {
        $workspace = Workspace::factory()->create();
        $data = [
            'workspace_id' => $workspace->id,
            'name' => 'Hour',
            'abbreviation' => 'hr',
        ];

        $productUnit = $this->productUnitService->createProductUnit($data);

        $this->assertDatabaseHas('product_units', [
            'workspace_id' => $workspace->id,
            'name' => 'Hour',
            'abbreviation' => 'hr',
        ]);
        $this->assertEquals('Hour', $productUnit->name);
        $this->assertEquals('hr', $productUnit->abbreviation);
    }

    public function test_can_update_product_unit(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);
        $data = [
            'name' => 'Updated Name',
            'abbreviation' => 'un',
        ];

        $updatedProductUnit = $this->productUnitService->updateProductUnit($productUnit, $data);

        $this->assertEquals('Updated Name', $updatedProductUnit->name);
        $this->assertEquals('un', $updatedProductUnit->abbreviation);
    }

    public function test_can_delete_product_unit(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);

        $this->productUnitService->deleteProductUnit($productUnit);

        $this->assertDatabaseMissing('product_units', ['id' => $productUnit->id]);
    }

    public function test_can_get_product_units_by_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();

        ProductUnit::factory()->count(3)->create(['workspace_id' => $workspace->id]);
        ProductUnit::factory()->count(2)->create(['workspace_id' => $otherWorkspace->id]);

        $productUnits = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        $this->assertCount(3, $productUnits);
    }

    public function test_can_get_product_unit_by_id(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);

        $foundProductUnit = $this->productUnitService->getProductUnitById($productUnit->id);

        $this->assertNotNull($foundProductUnit);
        $this->assertEquals($productUnit->id, $foundProductUnit->id);
    }

    public function test_product_unit_belongs_to_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $productUnit = ProductUnit::factory()->create(['workspace_id' => $workspace->id]);

        $this->assertEquals($workspace->id, $productUnit->workspace->id);
    }
}

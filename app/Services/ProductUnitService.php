<?php

namespace App\Services;

use App\Models\ProductUnit;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProductUnitService
{
    public function createProductUnit(array $data): ProductUnit
    {
        try {
            return ProductUnit::create([
                'workspace_id' => $data['workspace_id'],
                'name' => $data['name'],
                'abbreviation' => $data['abbreviation'],
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to create product unit: '.$e->getMessage());
        }
    }

    public function updateProductUnit(ProductUnit $productUnit, array $data): ProductUnit
    {
        try {
            $productUnit->update([
                'name' => $data['name'] ?? $productUnit->name,
                'abbreviation' => $data['abbreviation'] ?? $productUnit->abbreviation,
            ]);

            return $productUnit;
        } catch (Exception $e) {
            throw new Exception('Failed to update product unit: '.$e->getMessage());
        }
    }

    public function deleteProductUnit(ProductUnit $productUnit): void
    {
        try {
            if ($productUnit->products()->exists()) {
                throw new Exception('Cannot delete product unit with existing products.');
            }
            $productUnit->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete product unit: '.$e->getMessage());
        }
    }

    public function getAllProductUnits(): Collection
    {
        return ProductUnit::all();
    }

    public function getProductUnitsByWorkspace(int $workspaceId): Collection
    {
        return ProductUnit::where('workspace_id', $workspaceId)->get();
    }

    public function getProductUnitById(int $id): ?ProductUnit
    {
        return ProductUnit::find($id);
    }
}

<?php

namespace App\Services;

use App\Enums\BillingType;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function createProduct(array $data): Product
    {
        try {
            return Product::create([
                'workspace_id' => $data['workspace_id'],
                'unit_id' => $data['unit_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'] ?? null,
                'unit_price' => $data['unit_price'] ?? 0,
                'billing_type' => $data['billing_type'] ?? BillingType::OneTime->value,
                'is_active' => $data['is_active'] ?? true,
            ]);
        } catch (\Exception $e) {
            throw new Exception('Failed to create product: ' . $e->getMessage());
        }
    }

    public function updateProduct(Product $product, array $data): Product
    {
        try {
            $product->update([
                'unit_id' => $data['unit_id'] ?? $product->unit_id,
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'sku' => $data['sku'] ?? $product->sku,
                'unit_price' => $data['unit_price'] ?? $product->unit_price,
                'billing_type' => $data['billing_type'] ?? $product->billing_type,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]);

            return $product;
        } catch (\Exception $e) {
            throw new Exception('Failed to update product: ' . $e->getMessage());
        }
    }

    public function deleteProduct(Product $product): void
    {
        try {
            $product->delete();
        } catch (\Exception $e) {
            throw new Exception('Failed to delete product: ' . $e->getMessage());
        }
    }

    public function getAllProducts(): Collection
    {
        return Product::with('unit')->get();
    }

    public function getProductsByWorkspace(int $workspaceId): Collection
    {
        return Product::with('unit')->where('workspace_id', $workspaceId)->get();
    }

    public function getActiveProductsByWorkspace(int $workspaceId): Collection
    {
        return Product::with('unit')
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->get();
    }

    public function getProductById(int $id): ?Product
    {
        return Product::with('unit')->find($id);
    }
}

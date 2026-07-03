<?php

namespace App\Services;

use App\Enums\BillingFrequency;
use App\Enums\BillingType;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(private ActivityService $activityService) {}

    public function createProduct(array $data): Product
    {
        try {
            $product = Product::create([
                'workspace_id' => $data['workspace_id'],
                'unit_id' => $data['unit_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'] ?? null,
                'unit_price' => $data['unit_price'] ?? 0,
                'billing_type' => $data['billing_type'] ?? BillingType::OneTime->value,
                'billing_frequency' => $data['billing_frequency'] ?? BillingFrequency::None->value,
                'is_active' => $data['is_active'] ?? true,
            ]);
            $this->activityService->created($product);

            return $product;
        } catch (Exception $e) {
            throw new Exception('Failed to create product: '.$e->getMessage());
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
                'billing_type' => $data['billing_type'] ?? $product->billing_type?->value ?? $product->billing_type,
                'billing_frequency' => $data['billing_frequency'] ?? $product->billing_frequency?->value ?? $product->billing_frequency,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]);

            $this->activityService->updated($product);

            return $product;
        } catch (Exception $e) {
            throw new Exception('Failed to update product: '.$e->getMessage());
        }
    }

    public function deleteProduct(Product $product): void
    {
        try {
            $this->activityService->deleted($product);
            $product->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete product: '.$e->getMessage());
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

    public function bulkUpdateStatus(array $productIds, bool $isActive): int
    {
        return Product::whereIn('id', $productIds)->update(['is_active' => $isActive]);
    }

    public function bulkDelete(array $productIds): int
    {
        return Product::whereIn('id', $productIds)->delete();
    }

    public function getFilteredProducts(int $workspaceId, ?string $status = null, ?string $search = null): array
    {
        $query = Product::query()->where('workspace_id', $workspaceId);

        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->with('unit')->get();

        $stats = $this->getProductStats($workspaceId);

        return [
            'products' => $products,
            'stats' => $stats,
        ];
    }

    private function getProductStats(int $workspaceId): array
    {
        $total = Product::where('workspace_id', $workspaceId)->count();
        $active = Product::where('workspace_id', $workspaceId)->where('is_active', true)->count();
        $inactive = Product::where('workspace_id', $workspaceId)->where('is_active', false)->count();

        return [
            'total' => [
                'value' => $total,
            ],
            'active' => [
                'value' => $active,
            ],
            'inactive' => [
                'value' => $inactive,
            ],
        ];
    }
}

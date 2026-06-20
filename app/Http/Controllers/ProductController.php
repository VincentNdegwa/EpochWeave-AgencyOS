<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Activity;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductUnitService $productUnitService
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $units = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        $result = $this->productService->getFilteredProducts(
            $workspace->id,
            $request->query('status'),
            $request->query('search')
        );

        return Inertia::render('product/index', [
            'products' => $result['products'],
            'units' => $units,
            'stats' => $result['stats'],
            'filters' => [
                'status' => $request->query('status', 'all'),
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $product = $this->productService->createProduct($data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product created successfully.']);

            return redirect()->route('products.show', $product);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function show(int $id)
    {
        $product = $this->productService->getProductById($id);

        if (! $product) {
            abort(404);
        }

        $activities = Activity::where('subject_type', \App\Models\Product::class)
            ->where('subject_id', $product->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return Inertia::render('product/show', [
            'product' => $product,
            'activities' => $activities,
        ]);
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            if (! $product) {
                abort(404);
            }

            $this->productService->updateProduct($product, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product updated successfully.']);

            return redirect()->route('products.show', $product->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            if (! $product) {
                abort(404);
            }

            $this->productService->deleteProduct($product);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product deleted successfully.']);

            return redirect()->route('products.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id',
            'is_active' => 'required|boolean',
        ]);

        try {
            $updated = $this->productService->bulkUpdateStatus($request->input('ids'), $request->boolean('is_active'));

            Inertia::flash('toast', ['type' => 'success', 'message' => "Successfully updated {$updated} products."]);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to update products.']);

            return redirect()->back();
        }
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id',
        ]);

        try {
            $deleted = $this->productService->bulkDelete($request->input('ids'));

            Inertia::flash('toast', ['type' => 'success', 'message' => "Successfully deleted {$deleted} products."]);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to delete products.']);

            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use Exception;
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
        $products = $this->productService->getProductsByWorkspace($workspace->id);
        $units = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        return Inertia::render('product/index', [
            'products' => $products,
            'units' => $units,
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

        if (!$product) {
            abort(404);
        }

        return Inertia::render('product/show', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            if (!$product) {
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

            if (!$product) {
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
}

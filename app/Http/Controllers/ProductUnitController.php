<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductUnitRequest;
use App\Http\Requests\UpdateProductUnitRequest;
use App\Services\ProductUnitService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductUnitController extends Controller
{
    public function __construct(
        private ProductUnitService $productUnitService
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $productUnits = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        return Inertia::render('product-unit/index', [
            'product_units' => $productUnits,
        ]);
    }

    public function store(StoreProductUnitRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $productUnit = $this->productUnitService->createProductUnit($data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product unit created successfully.']);

            return redirect()->route('product-units.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateProductUnitRequest $request, int $id): RedirectResponse
    {
        try {
            $productUnit = $this->productUnitService->getProductUnitById($id);

            if (!$productUnit) {
                abort(404);
            }

            $this->productUnitService->updateProductUnit($productUnit, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product unit updated successfully.']);

            return redirect()->route('product-units.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $productUnit = $this->productUnitService->getProductUnitById($id);

            if (!$productUnit) {
                abort(404);
            }

            $this->productUnitService->deleteProductUnit($productUnit);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Product unit deleted successfully.']);

            return redirect()->route('product-units.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back();
        }
    }
}

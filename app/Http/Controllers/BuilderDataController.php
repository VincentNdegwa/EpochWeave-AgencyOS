<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Models\ProposalTemplate;
use App\Services\AccountService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderDataController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductUnitService $productUnitService,
        private AccountService $accountService
    ) {}

    public function products(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $products = $this->productService->getActiveProductsByWorkspace($workspace->id);

        return response()->json($products);
    }

    public function units(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $units = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        return response()->json($units);
    }

    public function templates(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $templates = ProposalTemplate::where('workspace_id', $workspace->id)->get();

        return response()->json($templates);
    }

    public function template(Request $request, ProposalTemplate $template): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        
        // Ensure the template belongs to the current workspace
        if ($template->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        return response()->json($template);
    }

    public function accounts(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $accounts = $this->accountService->getAccountsByWorkspace($workspace->id)
            ->where('status', '!=', AccountStatus::Archived->value);

        return response()->json($accounts);
    }
}

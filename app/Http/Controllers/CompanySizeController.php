<?php

namespace App\Http\Controllers;

use App\Exceptions\CompanySizeException;
use App\Http\Requests\StoreCompanySizeRequest;
use App\Http\Requests\UpdateCompanySizeRequest;
use App\Models\CompanySize;
use App\Services\CompanySizeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySizeController extends Controller
{
    public function __construct(private readonly CompanySizeService $companySizeService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $search = $request->input('search');
        $companySizes = $this->companySizeService->listForWorkspace($workspace->id, $search);

        return Inertia::render('setup/company-size/index', [
            'company_sizes' => $companySizes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreCompanySizeRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->companySizeService->createCompanySize($data);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Company size created successfully.']);
        } catch (CompanySizeException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(UpdateCompanySizeRequest $request, CompanySize $companySize): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($companySize->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->companySizeService->updateCompanySize($companySize, $request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Company size updated successfully.']);
        } catch (CompanySizeException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, CompanySize $companySize): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($companySize->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->companySizeService->deleteCompanySize($companySize);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Company size deleted successfully.']);
        } catch (CompanySizeException $e) {
            return redirect()->back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

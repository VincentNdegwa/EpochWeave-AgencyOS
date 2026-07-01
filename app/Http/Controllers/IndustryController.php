<?php

namespace App\Http\Controllers;

use App\Exceptions\IndustryException;
use App\Http\Requests\StoreIndustryRequest;
use App\Http\Requests\UpdateIndustryRequest;
use App\Models\Industry;
use App\Services\IndustryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndustryController extends Controller
{
    public function __construct(private readonly IndustryService $industryService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $search = $request->input('search');
        $industries = $this->industryService->listForWorkspace($workspace->id, $search);

        return Inertia::render('setup/industry/index', [
            'industries' => $industries,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreIndustryRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->industryService->createIndustry($data);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Industry created successfully.']);
        } catch (IndustryException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(UpdateIndustryRequest $request, Industry $industry): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($industry->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->industryService->updateIndustry($industry, $request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Industry updated successfully.']);
        } catch (IndustryException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, Industry $industry): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($industry->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->industryService->deleteIndustry($industry);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Industry deleted successfully.']);
        } catch (IndustryException $e) {
            return redirect()->back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

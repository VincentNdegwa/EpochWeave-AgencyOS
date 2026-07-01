<?php

namespace App\Http\Controllers;

use App\Exceptions\LeadSourceException;
use App\Http\Requests\StoreLeadSourceRequest;
use App\Http\Requests\UpdateLeadSourceRequest;
use App\Models\LeadSource;
use App\Services\LeadSourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadSourceController extends Controller
{
    public function __construct(private readonly LeadSourceService $leadSourceService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $search = $request->input('search');
        $leadSources = $this->leadSourceService->listForWorkspace($workspace->id, $search);

        return Inertia::render('setup/lead-source/index', [
            'lead_sources' => $leadSources,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreLeadSourceRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->leadSourceService->createLeadSource($data);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Lead source created successfully.']);
        } catch (LeadSourceException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(UpdateLeadSourceRequest $request, LeadSource $leadSource): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($leadSource->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->leadSourceService->updateLeadSource($leadSource, $request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Lead source updated successfully.']);
        } catch (LeadSourceException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, LeadSource $leadSource): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($leadSource->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->leadSourceService->deleteLeadSource($leadSource);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Lead source deleted successfully.']);
        } catch (LeadSourceException $e) {
            return redirect()->back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

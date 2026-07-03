<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEngagementRequest;
use App\Http\Requests\UpdateEngagementRequest;
use App\Models\Engagement;
use App\Services\EngagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EngagementController extends Controller
{
    public function __construct(private EngagementService $engagementService) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');

        $engagements = Engagement::where('workspace_id', $workspace->id)
            ->with(['account:id,company_name', 'user:id,name'])
            ->orderByDesc('completed_at')
            ->orderByDesc('scheduled_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('engagements/Index', [
            'engagements' => $engagements,
        ]);
    }

    public function store(StoreEngagementRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $this->engagementService->createEngagement($workspace->id, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement logged successfully.']);

        return redirect()->route('accounts.index');
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        $this->engagementService->updateEngagement($engagement, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement updated successfully.']);

        return redirect()->back();
    }

    public function destroy(Engagement $engagement): RedirectResponse
    {
        $this->engagementService->deleteEngagement($engagement);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement deleted successfully.']);

        return redirect()->back();
    }
}

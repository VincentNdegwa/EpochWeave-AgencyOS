<?php

namespace App\Http\Controllers;

use App\Enums\EngagementDirection;
use App\Enums\EngagementOutcome;
use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Models\Engagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EngagementController extends Controller
{
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

    public function store(Request $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:'.implode(',', array_column(EngagementType::cases(), 'value')),
            'direction' => 'required|in:'.implode(',', array_column(EngagementDirection::cases(), 'value')),
            'status' => 'required|in:'.implode(',', array_column(EngagementStatus::cases(), 'value')),
            'subject' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'proposal_id' => 'nullable|exists:proposals,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'project_id' => 'nullable|exists:projects,id',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'follow_up_at' => 'nullable|date',
            'outcome' => 'nullable|in:'.implode(',', array_column(EngagementOutcome::cases(), 'value')),
        ]);

        $data['workspace_id'] = $workspace->id;

        Engagement::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement logged successfully.']);

        return redirect()->back();
    }

    public function update(Request $request, Engagement $engagement): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:'.implode(',', array_column(EngagementType::cases(), 'value')),
            'direction' => 'required|in:'.implode(',', array_column(EngagementDirection::cases(), 'value')),
            'status' => 'required|in:'.implode(',', array_column(EngagementStatus::cases(), 'value')),
            'subject' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'proposal_id' => 'nullable|exists:proposals,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'project_id' => 'nullable|exists:projects,id',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'follow_up_at' => 'nullable|date',
            'outcome' => 'nullable|in:'.implode(',', array_column(EngagementOutcome::cases(), 'value')),
        ]);

        $engagement->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement updated successfully.']);

        return redirect()->back();
    }

    public function destroy(Engagement $engagement): RedirectResponse
    {
        $engagement->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Engagement deleted successfully.']);

        return redirect()->back();
    }
}

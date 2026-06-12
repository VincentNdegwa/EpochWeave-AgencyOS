<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProposalStatusRequest;
use App\Http\Requests\UpdateProposalStatusRequest;
use App\Models\ProposalStatus;
use App\Services\ProposalStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalStatusController extends Controller
{
    public function __construct(
        private ProposalStatusService $proposalStatusService
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $proposalStatuses = $this->proposalStatusService->getStatusesForWorkspace($workspace);

        return Inertia::render('proposal-status/index', [
            'proposal_statuses' => $proposalStatuses,
        ]);
    }

    public function store(StoreProposalStatusRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $proposalStatus = $this->proposalStatusService->createStatus($workspace, $data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal status created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateProposalStatusRequest $request, int $id): RedirectResponse
    {
        try {
            $proposalStatus = $this->proposalStatusService->getStatusById($id);

            if (! $proposalStatus) {
                abort(404);
            }

            $this->proposalStatusService->updateStatus($proposalStatus, $request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal status updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $proposalStatus = $this->proposalStatusService->getStatusById($id);

            if (! $proposalStatus) {
                abort(404);
            }

            $this->proposalStatusService->deleteStatus($proposalStatus);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal status deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function getStatusById(int $id): ?ProposalStatus
    {
        return ProposalStatus::find($id);
    }
}

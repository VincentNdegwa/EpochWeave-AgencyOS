<?php

namespace App\Http\Controllers;

use App\Services\ProposalService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $proposals = $this->proposalService->getProposalsByWorkspace($workspace->id);

        return Inertia::render('proposals/index', [
            'proposals' => $proposals,
        ]);
    }

    public function create(Request $request)
    {

        return Inertia::render('proposals/create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = $request->validate([
                'account_id' => 'required|exists:accounts,id',
                'title' => 'required|string|max:255',
                'currency' => 'required|string',
                'valid_until' => 'nullable|date',
            ]);

            $data = array_merge($data, [
                'workspace_id' => $workspace->id,
                'status' => 'draft',
                'blocks' => [],
                'total_amount' => 0,
                'token' => \Illuminate\Support\Str::uuid(),
            ]);

            $proposal = $this->proposalService->createProposal($data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal created successfully.']);

            return redirect()->route('proposals.show', $proposal->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function show(int $id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        if (!$proposal) {
            abort(404);
        }

        return Inertia::render('proposal/show', [
            'proposal' => $proposal,
        ]);
    }

    public function edit(int $id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        if (!$proposal) {
            abort(404);
        }

        return Inertia::render('proposal/edit', [
            'proposal' => $proposal,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                abort(404);
            }

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'currency' => 'sometimes|required|string',
                'valid_until' => 'nullable|date',
                'status' => 'sometimes|required|string',
                'total_amount' => 'sometimes|required|numeric',
                'blocks' => 'sometimes|array',
            ]);

            $this->proposalService->updateProposal($proposal, $data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal updated successfully.']);

            return redirect()->route('proposals.show', $proposal->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                abort(404);
            }

            $this->proposalService->deleteProposal($proposal);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal deleted successfully.']);

            return redirect()->route('proposals.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\UpdateProposalRequest;
use App\Models\Proposal;
use App\Models\WorkspaceSetting;
use App\Services\ProposalService;
use App\Services\WorkspaceSettingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService,
        private WorkspaceSettingService $workspaceSettingService
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

    public function store(StoreProposalRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = $request->validated();
            $content = $data['blocks'] ?? [];
            unset($data['blocks']);

            $settings = $this->workspaceSettingService->getOrCreate(
                $workspace->id,
                WorkspaceSetting::SUBMODULE_PROPOSALS
            );

            $numberingSettings = $settings->settings['numbering'] ?? [];
            $nextSequenceNumber = (int) ($numberingSettings['next_sequence_number'] ?? 1);
            $proposalNumber = $this->generateProposalNumber($numberingSettings);
            unset($data['proposal_number']);

            $data = array_merge($data, [
                'workspace_id' => $workspace->id,
                'created_by' => $request->user()->id,
                'proposal_number' => $proposalNumber,
                'status' => 'draft',
                'content' => $content,
                'token' => Str::uuid(),
            ]);

            $proposal = $this->proposalService->createProposal($data);

            $this->workspaceSettingService->incrementNumberingSequence($settings, $nextSequenceNumber);

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

        if (! $proposal) {
            abort(404);
        }

        return Inertia::render('proposal/show', [
            'proposal' => $proposal,
        ]);
    }

    public function edit(int $id)
    {
        $proposal = $this->proposalService->getProposalById($id);

        if (! $proposal) {
            abort(404);
        }

        return Inertia::render('proposal/edit', [
            'proposal' => $proposal,
        ]);
    }

    public function update(UpdateProposalRequest $request, int $id): RedirectResponse
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);

            if (! $proposal) {
                abort(404);
            }

            $data = $request->validated();

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

            if (! $proposal) {
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

    private function generateProposalNumber(array $numberingSettings): string
    {
        $format = $numberingSettings['format'] ?? '{PREFIX}{DELIMITER}{SEQUENCE}';
        $prefix = $numberingSettings['prefix'] ?? 'PROP';
        $delimiter = $numberingSettings['delimiter'] ?? '-';
        $sequencePadding = max(0, (int) ($numberingSettings['sequence_padding'] ?? 4));
        $nextSequenceNumber = (int) ($numberingSettings['next_sequence_number'] ?? 1);

        $sequence = $sequencePadding > 0
            ? str_pad((string) $nextSequenceNumber, $sequencePadding, '0', STR_PAD_LEFT)
            : (string) $nextSequenceNumber;

        return strtr($format, [
            '{PREFIX}' => $prefix,
            '{YEAR}' => now()->format('Y'),
            '{DELIMITER}' => $delimiter,
            '{SEQUENCE}' => $sequence,
        ]);
    }
}

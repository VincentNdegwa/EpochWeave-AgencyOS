<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProposalStatusRequest;
use App\Http\Requests\UpdateProposalStatusRequest;
use App\Models\ProposalStatus;
use App\Models\Workspace;
use App\Services\ProposalStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalStatusController extends Controller
{
    public function __construct(private ProposalStatusService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace();
        $statuses = $this->service->getStatusesForWorkspace($workspace);

        return response()->json($statuses);
    }

    public function store(StoreProposalStatusRequest $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace();
        $status = $this->service->createStatus($workspace, $request->validated());

        return response()->json($status, 201);
    }

    public function show(ProposalStatus $status): JsonResponse
    {
        return response()->json($status);
    }

    public function update(UpdateProposalStatusRequest $request, ProposalStatus $status): JsonResponse
    {
        $status = $this->service->updateStatus($status, $request->validated());

        return response()->json($status);
    }

    public function destroy(ProposalStatus $status): JsonResponse
    {
        $this->service->deleteStatus($status);

        return response()->json(null, 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['status_ids' => 'required|array']);
        $workspace = $request->user()->currentWorkspace();
        
        $this->service->reorderStatuses($workspace, $request->status_ids);

        return response()->json(null, 204);
    }
}

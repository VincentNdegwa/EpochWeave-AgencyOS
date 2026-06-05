<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function __construct(
        private WorkspaceService $workspaceService
    ) {}

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $workspace = $this->workspaceService->createWorkspace(
            $request->user(),
            $request->validated()
        );

        session(['current_workspace_id' => $workspace->id]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Workspace created successfully.']);

        return redirect()->back();
    }

    public function switch(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->workspaceService->switchWorkspace($request->user(), $workspace);

        return redirect()->back();
    }
}

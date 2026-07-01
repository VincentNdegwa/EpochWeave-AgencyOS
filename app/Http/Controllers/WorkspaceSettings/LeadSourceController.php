<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Services\LeadSourceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadSourceController extends Controller
{
    public function __construct(private readonly LeadSourceService $leadSourceService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('setup/lead-source/index', [
            'lead_sources' => $this->leadSourceService->listForWorkspace($workspace->id),
        ]);
    }
}

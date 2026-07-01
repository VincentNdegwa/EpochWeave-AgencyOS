<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Services\IndustryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndustryController extends Controller
{
    public function __construct(private readonly IndustryService $industryService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('setup/industry/index', [
            'industries' => $this->industryService->listForWorkspace($workspace->id),
        ]);
    }
}

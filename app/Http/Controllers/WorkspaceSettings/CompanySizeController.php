<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Services\CompanySizeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySizeController extends Controller
{
    public function __construct(private readonly CompanySizeService $companySizeService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('setup/company-size/index', [
            'company_sizes' => $this->companySizeService->listForWorkspace($workspace->id),
        ]);
    }
}

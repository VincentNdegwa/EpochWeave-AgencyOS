<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        return Inertia::render('dashboard/index', [
            'kpiData' => $this->dashboardService->getKpiData($workspace->id),
            'proposalFunnel' => $this->dashboardService->getProposalFunnel($workspace->id),
            'arAging' => $this->dashboardService->getArAging($workspace->id),
            'activeProjects' => $this->dashboardService->getActiveProjects($workspace->id),
            'topAccounts' => $this->dashboardService->getTopAccounts($workspace->id),
            'recentActivity' => $this->dashboardService->getRecentActivity($workspace->id),
            'upcomingDeadlines' => $this->dashboardService->getUpcomingDeadlines($workspace->id),
            'revenueChart' => $this->dashboardService->getRevenueChartData($workspace->id),
        ]);
    }
}

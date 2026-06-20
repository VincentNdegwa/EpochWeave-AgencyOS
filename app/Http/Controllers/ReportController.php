<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $year = (int) $request->input('year', now()->year);

        $revenueByMonth = Invoice::where('workspace_id', $workspace->id)
            ->whereYear('issue_date', $year)
            ->whereNotNull('issue_date')
            ->select(
                DB::raw('MONTH(issue_date) as month'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy(DB::raw('MONTH(issue_date)'))
            ->pluck('total', 'month')
            ->mapWithKeys(fn ($total, $month) => [$month => (float) $total])
            ->toArray();

        $revenueData = collect(range(1, 12))
            ->map(fn ($month) => $revenueByMonth[$month] ?? 0)
            ->values()
            ->toArray();

        $proposalStatusCounts = Proposal::where('workspace_id', $workspace->id)
            ->select('proposal_status_id', DB::raw('COUNT(*) as count'))
            ->groupBy('proposal_status_id')
            ->with('proposalStatus:id,title,color')
            ->get()
            ->map(fn ($p) => [
                'status' => $p->proposalStatus->title,
                'count' => (int) $p->count,
                'color' => $p->proposalStatus->color,
            ]);

        $topClients = Invoice::where('workspace_id', $workspace->id)
            ->with('account:id,company_name')
            ->select('account_id', DB::raw('SUM(grand_total) as total'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($i) => [
                'name' => $i->account?->company_name ?? 'Unknown',
                'total' => (float) $i->total,
            ]);

        $timeByMonth = TimeEntry::where('workspace_id', $workspace->id)
            ->whereYear('started_at', $year)
            ->whereNotNull('started_at')
            ->select(
                DB::raw('MONTH(started_at) as month'),
                DB::raw('SUM(duration_seconds) as seconds')
            )
            ->groupBy(DB::raw('MONTH(started_at)'))
            ->pluck('seconds', 'month')
            ->mapWithKeys(fn ($seconds, $month) => [$month => (int) $seconds])
            ->toArray();

        $timeData = collect(range(1, 12))
            ->map(fn ($month) => round(($timeByMonth[$month] ?? 0) / 3600, 2))
            ->values()
            ->toArray();

        return Inertia::render('reports/Index', [
            'year' => $year,
            'revenueByMonth' => $revenueData,
            'proposalStatusCounts' => $proposalStatusCounts,
            'topClients' => $topClients,
            'hoursByMonth' => $timeData,
        ]);
    }
}

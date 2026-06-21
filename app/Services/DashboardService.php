<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getRecentActivity(int $workspaceId): array
    {
        $proposals = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('signed_at')
            ->with('account:id,company_name')
            ->orderByDesc('signed_at')
            ->limit(5)
            ->get(['id', 'title', 'signed_at', 'grand_total', 'account_id'])
            ->map(fn ($p) => [
                'type' => 'proposal_signed',
                'title' => $p->title,
                'amount' => $p->grand_total,
                'account' => $p->account?->company_name,
                'date' => $p->signed_at->toIso8601String(),
            ]);

        $invoices = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('sent_at')
            ->with('account:id,company_name')
            ->orderByDesc('sent_at')
            ->limit(5)
            ->get(['id', 'invoice_number', 'sent_at', 'grand_total', 'account_id'])
            ->map(fn ($i) => [
                'type' => 'invoice_sent',
                'title' => $i->invoice_number,
                'amount' => $i->grand_total,
                'account' => $i->account?->company_name,
                'date' => $i->sent_at->toIso8601String(),
            ]);

        $tasks = Task::query()
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('completed_at')
            ->with('project:id,name')
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get(['id', 'title', 'completed_at', 'project_id'])
            ->map(fn ($t) => [
                'type' => 'task_completed',
                'title' => $t->title,
                'amount' => null,
                'project' => $t->project?->name,
                'date' => $t->completed_at->toIso8601String(),
            ]);

        return $proposals
            ->merge($invoices)
            ->merge($tasks)
            ->sortByDesc('date')
            ->values()
            ->take(10)
            ->all();
    }

    public function getUpcomingDeadlines(int $workspaceId): array
    {
        $today = now()->startOfDay();
        $weekFromNow = now()->addDays(7)->endOfDay();

        $proposals = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('valid_until', [$today, $weekFromNow])
            ->with('account:id,company_name')
            ->orderBy('valid_until')
            ->limit(5)
            ->get(['id', 'title', 'valid_until', 'account_id'])
            ->map(fn ($p) => [
                'type' => 'proposal_expiring',
                'title' => $p->title,
                'date' => $p->valid_until?->toDateString(),
                'account' => $p->account?->company_name,
            ]);

        $tasks = Task::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('due_date', [$today, $weekFromNow])
            ->whereNull('completed_at')
            ->with('project:id,name')
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'title', 'due_date', 'project_id'])
            ->map(fn ($t) => [
                'type' => 'task_due',
                'title' => $t->title,
                'date' => $t->due_date?->toDateString(),
                'project' => $t->project?->name,
            ]);

        $invoices = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('due_date', [$today, $weekFromNow])
            ->whereDoesntHave('invoiceStatus', fn ($q) => $q->where('automation_trigger', 'paid'))
            ->with('account:id,company_name')
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'invoice_number', 'due_date', 'account_id'])
            ->map(fn ($i) => [
                'type' => 'invoice_due',
                'title' => $i->invoice_number,
                'date' => $i->due_date?->toDateString(),
                'account' => $i->account?->company_name,
            ]);

        return $proposals
            ->merge($tasks)
            ->merge($invoices)
            ->sortBy('date')
            ->values()
            ->take(10)
            ->all();
    }

    public function getRevenueChartData(int $workspaceId): array
    {
        $months = collect(range(0, 11))->map(function (int $i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();

        $paidStatusIds = InvoiceStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'paid')
            ->pluck('id');

        $revenue = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('invoice_status_id', $paidStatusIds)
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $pipeline = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereDate('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        return [
            'labels' => $months->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->all(),
            'revenue' => $months->map(fn ($m) => (float) ($revenue[$m] ?? 0))->all(),
            'pipeline' => $months->map(fn ($m) => (float) ($pipeline[$m] ?? 0))->all(),
        ];
    }

    private function outstandingInvoicesCount(int $workspaceId): int
    {
        return Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('automation_trigger', ['sent', 'overdue']))
            ->count();
    }

    private function monthlyRevenue(int $workspaceId): float
    {
        $paidStatusIds = InvoiceStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'paid')
            ->pluck('id');

        return (float) Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('invoice_status_id', $paidStatusIds)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('grand_total');
    }

    public function getKpiData(int $workspaceId): array
    {
        $months = collect(range(0, 5))->map(function (int $i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();

        $paidStatusIds = InvoiceStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'paid')
            ->pluck('id');

        $sentStatusIds = ProposalStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'sent')
            ->pluck('id');

        $acceptedStatusIds = ProposalStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'accepted')
            ->pluck('id');

        $revenueByMonth = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('invoice_status_id', $paidStatusIds)
            ->whereNotNull('paid_at')
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereDate('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $pipelineByMonth = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $sentProposalsByMonth = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('proposal_status_id', $sentStatusIds)
            ->select(
                DB::raw("DATE_FORMAT(sent_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('sent_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->all();

        $acceptedProposalsByMonth = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('proposal_status_id', $acceptedStatusIds)
            ->select(
                DB::raw("DATE_FORMAT(accepted_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('accepted_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->all();

        $activeProjectsByMonth = Project::query()
            ->where('workspace_id', $workspaceId)
            ->whereNull('completed_at')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->all();

        $avgDealByMonth = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('AVG(grand_total) as avg')
            )
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('avg', 'month')
            ->all();

        $outstandingByMonth = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('automation_trigger', ['sent', 'overdue']))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->all();

        return [
            'revenue' => [
                'value' => $this->monthlyRevenue($workspaceId),
                'sparkline' => $months->map(fn ($m) => (float) ($revenueByMonth[$m] ?? 0))->all(),
                'change' => $this->calculateChange(
                    (float) ($revenueByMonth[$months->last()] ?? 0),
                    (float) ($revenueByMonth[$months->get($months->count() - 2)] ?? 0),
                ),
            ],
            'outstanding' => [
                'value' => $this->outstandingInvoicesCount($workspaceId),
                'sparkline' => $months->map(fn ($m) => (int) ($outstandingByMonth[$m] ?? 0))->all(),
                'change' => $this->calculateChange(
                    (int) ($outstandingByMonth[$months->last()] ?? 0),
                    (int) ($outstandingByMonth[$months->get($months->count() - 2)] ?? 0),
                    true,
                ),
            ],
            'pipeline' => [
                'value' => (float) Proposal::query()
                    ->where('workspace_id', $workspaceId)
                    ->whereHas('proposalStatus', fn ($q) => $q->where('automation_trigger', 'sent'))
                    ->sum('grand_total'),
                'sparkline' => $months->map(fn ($m) => (float) ($pipelineByMonth[$m] ?? 0))->all(),
                'change' => $this->calculateChange(
                    (float) ($pipelineByMonth[$months->last()] ?? 0),
                    (float) ($pipelineByMonth[$months->get($months->count() - 2)] ?? 0),
                ),
            ],
            'win_rate' => [
                'value' => $this->winRate($workspaceId),
                'sparkline' => $months->map(function ($m) use ($sentProposalsByMonth, $acceptedProposalsByMonth) {
                    $sent = (int) ($sentProposalsByMonth[$m] ?? 0);
                    $accepted = (int) ($acceptedProposalsByMonth[$m] ?? 0);

                    return $sent > 0 ? round(($accepted / $sent) * 100, 1) : 0;
                })->all(),
                'change' => $this->calculateChange(
                    $this->winRateForMonth($workspaceId, $months->last(), $sentStatusIds, $acceptedStatusIds),
                    $this->winRateForMonth($workspaceId, $months->get($months->count() - 2), $sentStatusIds, $acceptedStatusIds),
                ),
            ],
            'active_projects' => [
                'value' => Project::query()
                    ->where('workspace_id', $workspaceId)
                    ->whereNull('completed_at')
                    ->whereNull('archived_at')
                    ->count(),
                'sparkline' => $months->map(fn ($m) => (int) ($activeProjectsByMonth[$m] ?? 0))->all(),
                'change' => $this->calculateChange(
                    (int) ($activeProjectsByMonth[$months->last()] ?? 0),
                    (int) ($activeProjectsByMonth[$months->get($months->count() - 2)] ?? 0),
                ),
            ],
            'avg_deal_size' => [
                'value' => (float) Proposal::query()
                    ->where('workspace_id', $workspaceId)
                    ->avg('grand_total') ?? 0,
                'sparkline' => $months->map(fn ($m) => round((float) ($avgDealByMonth[$m] ?? 0), 2))->all(),
                'change' => $this->calculateChange(
                    (float) ($avgDealByMonth[$months->last()] ?? 0),
                    (float) ($avgDealByMonth[$months->get($months->count() - 2)] ?? 0),
                ),
            ],
        ];
    }

    public function getProposalFunnel(int $workspaceId): array
    {
        $statuses = ProposalStatus::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get(['id', 'title', 'color', 'automation_trigger']);

        return $statuses->map(fn ($status) => [
            'stage' => $status->title,
            'count' => Proposal::query()
                ->where('workspace_id', $workspaceId)
                ->where('proposal_status_id', $status->id)
                ->count(),
            'color' => $status->color,
        ])->all();
    }

    public function getArAging(int $workspaceId): array
    {
        $today = now()->startOfDay();

        $invoices = Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('invoiceStatus', fn ($q) => $q->whereIn('automation_trigger', ['sent', 'overdue']))
            ->whereNotNull('due_date')
            ->select('grand_total', 'due_date')
            ->get();

        $buckets = [
            ['label' => 'Current', 'min' => null, 'max' => 0, 'color' => '#22c55e'],
            ['label' => '1-30 days', 'min' => 1, 'max' => 30, 'color' => '#3b82f6'],
            ['label' => '31-60 days', 'min' => 31, 'max' => 60, 'color' => '#f59e0b'],
            ['label' => '61+ days', 'min' => 61, 'max' => null, 'color' => '#ef4444'],
        ];

        return collect($buckets)->map(function ($bucket) use ($invoices, $today) {
            $filtered = $invoices->filter(function ($invoice) use ($bucket, $today) {
                $daysOverdue = $today->diffInDays($invoice->due_date, false) * -1;

                if ($bucket['min'] === null && $bucket['max'] === 0) {
                    return $daysOverdue <= 0;
                }

                if ($bucket['max'] === null) {
                    return $daysOverdue >= $bucket['min'];
                }

                return $daysOverdue >= $bucket['min'] && $daysOverdue <= $bucket['max'];
            });

            return [
                'label' => $bucket['label'],
                'amount' => (float) $filtered->sum('grand_total'),
                'count' => $filtered->count(),
                'color' => $bucket['color'],
            ];
        })->all();
    }

    public function getActiveProjects(int $workspaceId): array
    {
        return Project::query()
            ->where('workspace_id', $workspaceId)
            ->whereNull('completed_at')
            ->whereNull('archived_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'name', 'color', 'tasks_total', 'tasks_completed'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'completion' => $p->tasks_total > 0
                    ? round(($p->tasks_completed / $p->tasks_total) * 100)
                    : 0,
                'color' => $p->color,
            ])
            ->all();
    }

    public function getTopAccounts(int $workspaceId): array
    {
        $paidStatusIds = InvoiceStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('automation_trigger', 'paid')
            ->pluck('id');

        return Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('invoice_status_id', $paidStatusIds)
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', now()->subYear()->startOfDay())
            ->with('account:id,company_name')
            ->select('account_id', DB::raw('SUM(grand_total) as total_revenue'))
            ->groupBy('account_id')
            ->orderByDesc('total_revenue')
            ->limit(6)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->account_id,
                'name' => $i->account?->company_name ?? 'Unknown',
                'revenue' => (float) $i->total_revenue,
            ])
            ->all();
    }

    private function winRate(int $workspaceId): float
    {
        $sent = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('proposalStatus', fn ($q) => $q->where('automation_trigger', 'sent'))
            ->count();

        $accepted = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('proposalStatus', fn ($q) => $q->where('automation_trigger', 'accepted'))
            ->count();

        return $sent > 0 ? round(($accepted / $sent) * 100, 1) : 0;
    }

    private function winRateForMonth(
        int $workspaceId,
        string $month,
        $sentStatusIds,
        $acceptedStatusIds,
    ): float {
        $sent = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('proposal_status_id', $sentStatusIds)
            ->whereYear('sent_at', substr($month, 0, 4))
            ->whereMonth('sent_at', substr($month, 5, 2))
            ->count();

        $accepted = Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereIn('proposal_status_id', $acceptedStatusIds)
            ->whereYear('accepted_at', substr($month, 0, 4))
            ->whereMonth('accepted_at', substr($month, 5, 2))
            ->count();

        return $sent > 0 ? round(($accepted / $sent) * 100, 1) : 0;
    }

    private function calculateChange(float|int $current, float|int $previous, bool $invert = false): ?array
    {
        if ($previous == 0) {
            return $current > 0
                ? ['value' => 100, 'label' => 'vs prior period']
                : null;
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        if ($invert) {
            $change = $change * -1;
        }

        return [
            'value' => $change,
            'label' => 'vs prior period',
        ];
    }
}

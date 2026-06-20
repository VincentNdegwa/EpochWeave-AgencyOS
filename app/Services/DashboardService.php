<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getWorkspaceStats(int $workspaceId): array
    {
        return [
            'open_proposals' => $this->openProposalsCount($workspaceId),
            'outstanding_invoices' => $this->outstandingInvoicesCount($workspaceId),
            'overdue_invoices' => $this->overdueInvoicesCount($workspaceId),
            'open_tasks' => $this->openTasksCount($workspaceId),
            'monthly_revenue' => $this->monthlyRevenue($workspaceId),
            'unbilled_hours' => $this->unbilledHours($workspaceId),
        ];
    }

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
            ->whereDoesntHave('status', fn ($q) => $q->where('automation_trigger', 'paid'))
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
        $months = collect(range(0, 5))->map(function (int $i) {
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
            ->whereDate('paid_at', '>=', now()->subMonths(5)->startOfMonth())
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
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
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

    private function openProposalsCount(int $workspaceId): int
    {
        return Proposal::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('proposalStatus', fn ($q) => $q->where('automation_trigger', 'sent'))
            ->count();
    }

    private function outstandingInvoicesCount(int $workspaceId): int
    {
        return Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('status', fn ($q) => $q->whereIn('automation_trigger', ['sent', 'overdue']))
            ->count();
    }

    private function overdueInvoicesCount(int $workspaceId): int
    {
        return Invoice::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('status', fn ($q) => $q->where('automation_trigger', 'overdue'))
            ->count();
    }

    private function openTasksCount(int $workspaceId): int
    {
        $closedStatusIds = TaskStatus::query()
            ->where('workspace_id', $workspaceId)
            ->where('is_closed', true)
            ->pluck('id');

        return Task::query()
            ->where('workspace_id', $workspaceId)
            ->whereNull('parent_id')
            ->whereNotIn('task_status_id', $closedStatusIds)
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

    private function unbilledHours(int $workspaceId): float
    {
        $seconds = TimeEntry::query()
            ->where('workspace_id', $workspaceId)
            ->where('is_billable', true)
            ->where('is_invoiced', false)
            ->whereNotNull('duration_seconds')
            ->sum('duration_seconds');

        return round($seconds / 3600, 2);
    }
}

export interface DashboardStats {
    open_proposals: number;
    outstanding_invoices: number;
    overdue_invoices: number;
    open_tasks: number;
    monthly_revenue: number;
    unbilled_hours: number;
}

export interface DashboardActivity {
    type: 'proposal_signed' | 'invoice_sent' | 'task_completed';
    title: string;
    amount: number | null;
    account?: string | null;
    project?: string | null;
    date: string;
}

export interface DashboardDeadline {
    type: 'proposal_expiring' | 'task_due' | 'invoice_due';
    title: string;
    date: string;
    account?: string | null;
    project?: string | null;
}

export interface RevenueChartData {
    labels: string[];
    revenue: number[];
    pipeline: number[];
}

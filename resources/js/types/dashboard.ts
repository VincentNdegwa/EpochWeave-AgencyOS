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

export interface KpiItem {
    value: number;
    sparkline: number[];
    change: { value: number; label: string } | null;
}

export interface KpiData {
    revenue: KpiItem;
    outstanding: KpiItem;
    pipeline: KpiItem;
    win_rate: KpiItem;
    active_projects: KpiItem;
    avg_deal_size: KpiItem;
}

export interface ProposalFunnelItem {
    stage: string;
    count: number;
    color: string;
}

export interface ArAgingBucket {
    label: string;
    amount: number;
    count: number;
    color: string;
}

export interface ActiveProject {
    id: number;
    name: string;
    completion: number;
    color: string | null;
}

export interface TopAccount {
    id: number;
    name: string;
    revenue: number;
}

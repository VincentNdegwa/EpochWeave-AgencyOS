export interface TimeEntry {
    id: number;
    workspace_id: number;
    project_id: number;
    task_id: number | null;
    user_id: number | null;
    description: string | null;
    started_at: string;
    ended_at: string;
    duration_seconds: number;
    is_billable: boolean;
    is_invoiced: boolean;
    hourly_rate: number | null;
    date: string;
    invoice_id: number | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    } | null;
}

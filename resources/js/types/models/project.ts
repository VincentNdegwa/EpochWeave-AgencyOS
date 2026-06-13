export interface Project {
    id: number;
    workspace_id: number;
    account_id: number;
    name: string;
    description: string | null;
    status: string;
    currency: string;
    start_date: string | null;
    due_date: string | null;
    portal_visible: boolean;
    created_at: string;
    updated_at: string;
}

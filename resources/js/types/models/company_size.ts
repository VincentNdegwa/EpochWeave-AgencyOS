export interface CompanySize {
    id: number;
    workspace_id: number;
    label: string;
    min_employees: number | null;
    max_employees: number | null;
    sort_order: number;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

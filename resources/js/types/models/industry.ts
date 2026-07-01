export interface Industry {
    id: number;
    workspace_id: number;
    name: string;
    color: string | null;
    description: string | null;
    sort_order: number;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

export interface TaskStatus {
    id: number;
    workspace_id: number;
    name: string;
    color: string | null;
    position: number;
    is_default: boolean;
    is_closed: boolean;
    created_at: string;
    updated_at: string;
}

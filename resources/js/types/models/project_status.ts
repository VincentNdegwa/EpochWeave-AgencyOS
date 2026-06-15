export interface ProjectStatus {
    id: number;
    workspace_id: number;
    title: string;
    color: string;
    is_system: boolean;
    automation_trigger:
        | 'planning'
        | 'active'
        | 'paused'
        | 'completed'
        | 'cancelled'
        | null;
    position: number;
    created_at: string;
    updated_at: string;
}

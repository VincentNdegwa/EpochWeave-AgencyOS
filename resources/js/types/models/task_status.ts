export interface TaskStatus {
    id: number;
    workspace_id: number;
    title: string;
    color: string;
    is_system: boolean;
    automation_trigger:
        | 'backlog'
        | 'unstarted'
        | 'active'
        | 'review'
        | 'completed'
        | 'cancelled'
        | null;
    position: number;
    created_at: string;
    updated_at: string;
}

export interface Note {
    id: number;
    workspace_id: number;
    noteable_type: string;
    noteable_id: number;
    user_id: number;
    body: string;
    is_internal: boolean;
    pinned_at: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    } | null;
}

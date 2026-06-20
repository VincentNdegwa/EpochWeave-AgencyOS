export interface Comment {
    id: number;
    workspace_id: number;
    commentable_type: string;
    commentable_id: number;
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

export interface Attachment {
    id: number;
    workspace_id: number;
    attachable_type: string;
    attachable_id: number;
    uploaded_by: number;
    disk: string;
    path: string;
    original_name: string;
    extension: string;
    mime_type: string;
    size: number;
    meta: Record<string, unknown> | null;
    created_at: string;
    updated_at: string;
    uploader?: {
        id: number;
        name: string;
    } | null;
}

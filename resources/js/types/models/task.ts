import type { Attachment } from './attachment';
import type { Comment } from './comment';
import type { Tag } from './tag';
import type { TaskStatus } from './task_status';
import type { TimeEntry } from './time_entry';

export interface Task {
    id: number;
    workspace_id: number;
    project_id: number;
    task_status_id: number;
    parent_id: number | null;
    title: string;
    description: string | null;
    assignee_id: number | null;
    priority: string;
    position: number;
    start_date: string | null;
    due_date: string | null;
    estimated_hours: number | null;
    is_billable: boolean;
    completed_at: string | null;
    created_by: number | null;
    created_at: string;
    updated_at: string;
    project?: {
        id: number;
        name: string;
        color: string | null;
    } | null;
    status?: TaskStatus | null;
    assignee?: {
        id: number;
        name: string;
        email: string;
    } | null;
    tags?: Tag[];
    children?: Task[];
    comments?: Comment[];
    attachments?: Attachment[];
    time_entries?: TimeEntry[];
    timeEntries?: TimeEntry[];
}

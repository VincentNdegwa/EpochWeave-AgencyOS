import type { Account } from './proposal';
import type { ProjectStatus } from './project_status';
import type { Task } from './task';

export interface ProjectMember {
    id: number;
    project_id: number;
    user_id: number;
    role: string | null;
    hourly_rate: number | null;
    joined_at: string | null;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

export interface Project {
    id: number;
    workspace_id: number;
    account_id: number;
    name: string;
    description: string | null;
    color: string | null;
    project_status_id: number;
    status?: ProjectStatus;
    hourly_rate: number | null;
    currency: string;
    start_date: string | null;
    due_date: string | null;
    tasks_total: number;
    tasks_completed: number;
    hours_logged: number | null;
    hours_budgeted: number | null;
    portal_visible: boolean;
    completed_at: string | null;
    archived_at: string | null;
    created_at: string;
    updated_at: string;
    account?: Account | null;
    members?: ProjectMember[];
    tasks?: Task[];
}

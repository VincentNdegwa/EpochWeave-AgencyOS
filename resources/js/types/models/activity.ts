import type { User } from '../auth';

export interface Activity {
    id: number;
    type: string;
    description: string;
    properties: Record<string, unknown> | null;
    user?: User;
    created_at: string;
    updated_at: string;
}

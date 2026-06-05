export interface Workspace {
    id: number;
    name: string;
    display_name: string | null;
    description: string | null;
    currency: string | null;
    white_label: boolean;
    domain: string | null;
    logo_url: string | null;
    primary_color: string | null;
    created_at: string;
    updated_at: string;
}

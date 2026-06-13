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
    address?: string | null;
    email?: string | null;
    phone?: string | null;
    tax_number?: string | null;
    payment_instructions?: string | null;
    created_at: string;
    updated_at: string;
}

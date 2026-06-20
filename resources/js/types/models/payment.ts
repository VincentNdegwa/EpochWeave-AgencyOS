export interface Payment {
    id: number;
    workspace_id: number;
    invoice_id: number;
    user_id: number;
    amount: number;
    method: string;
    paid_at: string;
    reference: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    } | null;
}

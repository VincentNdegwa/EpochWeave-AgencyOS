export interface CreditNote {
    id: number;
    workspace_id: number;
    invoice_id: number;
    payment_id: number | null;
    user_id: number;
    amount: number;
    reason: string | null;
    reference: string | null;
    refunded_at: string;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    } | null;
}

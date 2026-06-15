export interface InvoiceStatus {
    id: number;
    workspace_id: number;
    title: string;
    color: string;
    is_system: boolean;
    automation_trigger:
        | 'draft'
        | 'sent'
        | 'overdue'
        | 'paid'
        | 'voided'
        | null;
    position: number;
    created_at: string;
    updated_at: string;
}

import type { CreditNote } from './credit_note';
import type { InvoiceStatus } from './invoice_status';
import type { Payment } from './payment';
import type { Project } from './project';
import type { Account, AccountContact, Proposal, User } from './proposal';
import type { Workspace } from './workspace';

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    product_id: number | null;
    item_name: string;
    description: string | null;
    unit_label: string | null;
    quantity: number;
    unit_price: number;
    subtotal: number;
    discount_type: 'none' | 'percentage' | 'fixed' | null;
    discount_value: number | null;
    discount_amount: number | null;
    tax_type: 'none' | 'percentage' | 'fixed' | null;
    tax_value: number | null;
    total_tax_amount: number | null;
    total: number;
    position: number;
    created_at: string;
    updated_at: string;
    product?: {
        id: number;
        name: string;
        unit_price: number;
    } | null;
}

export interface Invoice {
    id: number;
    workspace_id: number;
    account_id: number | null;
    account_contact_id: number | null;
    proposal_id: number | null;
    project_id: number | null;
    created_by: number | null;
    user_id: number | null;
    invoice_number: string | null;
    token: string | null;
    payment_reference?: string | null;
    currency: string;
    subtotal: number;
    total_tax_amount: number;
    discount_total: number;
    grand_total: number;
    amount_paid: number;
    issue_date: string | null;
    due_date: string | null;
    notes: string | null;
    sent_at: string | null;
    paid_at: string | null;
    voided_at: string | null;
    created_at: string;
    updated_at: string;
    account?: Account | null;
    account_contact?: AccountContact | null;
    user?: User | null;
    proposal?: Proposal | null;
    project?: Project | null;
    workspace?: Workspace | null;
    items?: InvoiceItem[];
    payments?: Payment[];
    credit_notes?: CreditNote[];
    invoice_status?: InvoiceStatus | null;
}

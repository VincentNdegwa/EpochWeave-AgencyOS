import type { BaseBlock } from '../proposal-builder';
import type { Workspace } from './workspace';

export type DepositType = 'percentage' | 'fixed';

export interface ProposalStatusModel {
    id: number;
    workspace_id: number;
    title: string;
    color: string;
    is_system: boolean;
    automation_trigger:
        | 'draft'
        | 'sent'
        | 'accepted'
        | 'declined'
        | 'expired'
        | null;
    position: number;
    created_at: string;
    updated_at: string;
}

export interface Proposal extends Record<string, unknown> {
    id: number;
    workspace_id: number;
    account_id: number | null;
    account_contact_id: number | null;
    created_by: number | null;
    user_id: number | null;
    template_id: number | null;
    title: string;
    proposal_number: string | null;
    proposal_status_id: number | null;
    valid_until: string | null;
    content: BaseBlock[];
    currency: string;
    subtotal: number;
    discount_total: number;
    tax_rate: number | string;
    tax_amount: number;
    grand_total: number;
    requires_deposit: boolean;
    deposit_type: DepositType | null;
    deposit_value: number | null;
    deposit_amount: number | null;
    token: string | null;
    password_hash: string | null;
    signer_name: string | null;
    signer_email: string | null;
    signer_company: string | null;
    signature_data: Record<string, unknown> | null;
    signed_ip: string | null;
    signed_user_agent: string | null;
    deposit_invoice_id: number | null;
    project_id: number | null;
    sent_at: string | null;
    viewed_at: string | null;
    last_viewed_at: string | null;
    view_count: number;
    decided_at: string | null;
    expired_at: string | null;
    decline_reason: string | null;
    created_at: string;
    updated_at: string;
    account?: Account;
    account_contact?: AccountContact;
    user?: User;
    workspace?: Workspace;
    template?: ProposalTemplate;
    items?: ProposalItem[];
    proposal_status?: ProposalStatusModel;
}

export interface Account {
    id: number;
    company_name: string;
    status: string;
    website: string | null;
    lifetime_value: number;
    created_at: string;
    updated_at: string;
}

export interface AccountContact {
    id: number;
    account_id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    job_title: string | null;
    is_primary: boolean;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
}

export interface ProposalItem {
    id: number;
    proposal_id: number;
    product_id: number | null;
    item_name: string;
    description: string | null;
    unit_label: string | null;
    billing_type: 'one_time' | 'recurring';
    billing_frequency: 'none' | 'daily' | 'weekly' | 'monthly' | 'yearly';
    quantity: number;
    unit_price: number;
    subtotal: number;
    discount_type: 'percentage' | 'fixed' | null;
    discount_value: number | null;
    discount_amount: number | null;
    total: number;
    is_optional: boolean;
    is_selected: boolean;
    position: number;
    created_at: string;
    updated_at: string;
}

export interface ProposalTemplate {
    id: number;
    workspace_id: number;
    name: string;
    description: string | null;
    thumbnail_url: string | null;
    content: BaseBlock[];
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

export interface CreateProposal {
    account_id: number;
    title: string;
    currency: string;
    valid_until: string | null;
    template_id?: number | null;
    content?: BaseBlock[];
}

export interface UpdateProposal {
    title?: string;
    currency?: string;
    valid_until?: string | null;
    proposal_status_id?: number | null;
    account_contact_id?: number | null;
    user_id?: number | null;
    content?: BaseBlock[];
    subtotal?: number;
    discount_total?: number;
    tax_rate?: number | string;
    tax_amount?: number;
    grand_total?: number;
    requires_deposit?: boolean;
    deposit_type?: DepositType | null;
    deposit_value?: number | null;
    deposit_amount?: number | null;
}

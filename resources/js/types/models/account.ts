import type { Comment } from './comment';
import type { Invoice } from './invoice';
import type { Note } from './note';
import type { Project } from './project';
import type { Proposal } from './proposal';

export type AccountStatus = 'lead' | 'opportunity' | 'client' | 'archived';

export type EngagementType = 'call' | 'email' | 'meeting' | 'note' | 'task';

export type EngagementDirection = 'inbound' | 'outbound';

export type EngagementStatus = 'pending' | 'completed' | 'cancelled';

export type EngagementOutcome = 'successful' | 'unsuccessful' | 'neutral';

export interface Engagement {
    id: number;
    workspace_id: number;
    account_id: number;
    user_id: number | null;
    type: EngagementType;
    direction: EngagementDirection;
    status: EngagementStatus;
    subject: string | null;
    content: string | null;
    scheduled_at: string | null;
    completed_at: string | null;
    follow_up_at: string | null;
    outcome: EngagementOutcome | null;
    created_at: string;
    updated_at: string;
    user?: { id: number; name: string } | null;
    proposal?: { id: number; title: string } | null;
    invoice?: { id: number; invoice_number: string | null } | null;
    project?: { id: number; name: string } | null;
}

export interface CustomFieldValue {
    id: number;
    custom_field_definition_id: number;
    valueable_type: string;
    valueable_id: number;
    value_text: string | null;
    value_number: number | null;
    value_boolean: boolean | null;
    value_date: string | null;
    value_json: unknown;
    created_at: string;
    updated_at: string;
    definition?: {
        id: number;
        name: string;
        field_type: string;
    } | null;
    value?: string | number | boolean | unknown | null;
}

export interface PortalInvitation {
    id: number;
    account_id: number;
    account_contact_id: number;
    invitation_token: string;
    is_used: boolean;
    expires_at: string;
    created_at: string;
    updated_at: string;
    accountContact?: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
    } | null;
}

export interface Address {
    id: number;
    addressable_type: string;
    addressable_id: number;
    type: 'billing' | 'shipping' | 'primary' | 'office';
    label: string | null;
    street_1: string;
    street_2: string | null;
    city: string;
    state: string | null;
    postal_code: string | null;
    country: string;
    latitude: number | null;
    longitude: number | null;
    is_primary: boolean;
    created_at: string;
    updated_at: string;
}

export interface SocialProfile {
    id: number;
    profileable_type: string;
    profileable_id: number;
    platform: string;
    url: string;
    handle: string | null;
    followers_count: number | null;
    is_verified: boolean;
    created_at: string;
    updated_at: string;
}

export interface LookupOption {
    id: number;
    name: string;
    color: string | null;
}

export interface AccountContact {
    id: number;
    account_id: number;
    client_profile_id: number | null;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    job_title: string | null;
    date_of_birth: string | null;
    department: string | null;
    preferred_contact_method: string | null;
    notes: string | null;
    is_verified: boolean;
    is_primary: boolean;
    receives_billing: boolean;
    created_at: string;
    updated_at: string;

    account?: Account | null;
    clientProfile?: {
        id: number;
        email: string;
    } | null;
    portalInvitation?: {
        id: number;
        invitation_token: string;
        is_used: boolean;
        expires_at: string;
    } | null;
    addresses?: Address[] | null;
    socialProfiles?: SocialProfile[] | null;
}

export interface Account {
    id: number;
    workspace_id: number;
    company_name: string;
    phone: string | null;
    website: string | null;
    description: string | null;
    founded_at: string | null;
    status: AccountStatus;
    token: string | null;
    lifetime_value: number;
    annual_revenue: number | null;
    employee_count: number | null;
    industry_id: number | null;
    lead_source_id: number | null;
    company_size_id: number | null;
    created_at: string;
    updated_at: string;

    workspace?: {
        id: number;
        name: string;
        display_name: string;
    } | null;
    contacts?: AccountContact[] | null;
    addresses?: Address[] | null;
    socialProfiles?: SocialProfile[] | null;
    industry?: LookupOption | null;
    leadSource?: LookupOption | null;
    companySize?: { id: number; label: string } | null;
    proposals?: Proposal[] | null;
    projects?: Project[] | null;
    invoices?: Invoice[] | null;
    engagements?: Engagement[] | null;
    comments?: Comment[] | null;
    notes?: Note[] | null;
    customFieldValues?: CustomFieldValue[] | null;
    portalInvitations?: PortalInvitation[] | null;
}

export interface CreateAccountForm {
    company_name: string;
    status: AccountStatus;
    phone?: string | null;
    website?: string | null;
    description?: string | null;
    founded_at?: string | null;
    lifetime_value?: number | null;
    annual_revenue?: number | null;
    employee_count?: number | null;
    industry_id?: number | null;
    lead_source_id?: number | null;
    company_size_id?: number | null;
    contacts?: CreateContactForm[];
}

export type UpdateAccountForm = Partial<CreateAccountForm>;

export interface CreateContactForm {
    first_name: string;
    last_name: string;
    email: string;
    phone?: string | null;
    job_title?: string | null;
    date_of_birth?: string | null;
    department?: string | null;
    preferred_contact_method?: string | null;
    notes?: string | null;
    is_primary?: boolean;
    receives_billing?: boolean;
    send_invitation?: boolean;
}

export type UpdateContactForm = Partial<CreateContactForm>;

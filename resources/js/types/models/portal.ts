export interface BasePortalInvitation {
    id: number;
    account_id: number;
    account_contact_id: number;
    invitation_token: string;
    is_used: boolean;
    expires_at: string;
    created_at: string;
    updated_at: string;
}

export interface PortalInvitation extends BasePortalInvitation {
    account?: {
        id: number;
        company_name: string;
    } | null;
    accountContact?: {
        id: number;
        email: string;
    } | null;
}

export interface PortalSetupProps {
    token: string;
    email: string;
    company_name: string;
}

export type PortalSetupForm = {
    password: string;
    password_confirmation: string;
};

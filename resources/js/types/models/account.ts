export type AccountStatus = 'lead' | 'opportunity' | 'client' | 'archived';

export interface BaseAccount {
    id: number;
    workspace_id: number;
    company_name: string;
    website: string | null;
    status: AccountStatus;
    token: string | null;
    lifetime_value: number;
    created_at: string;
    updated_at: string;
}

export interface Account extends BaseAccount {
    workspace?: {
        id: number;
        name: string;
        display_name: string;
    } | null;
    contacts?: AccountContact[] | null;
}

export interface BaseAccountContact {
    id: number;
    account_id: number;
    client_profile_id: number | null;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    job_title: string | null;
    is_verified: boolean;
    is_primary: boolean;
    receives_billing: boolean;
    created_at: string;
    updated_at: string;
}

export interface AccountContact extends BaseAccountContact {
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
}

export type CreateAccountForm = Pick<
    BaseAccount,
    'company_name' | 'website' | 'status'
> & {
    contacts?: CreateContactForm[];
};

export type UpdateAccountForm = Partial<
    Pick<BaseAccount, 'company_name' | 'website' | 'status' | 'lifetime_value'>
>;

export type CreateContactForm = Pick<
    BaseAccountContact,
    | 'first_name'
    | 'last_name'
    | 'email'
    | 'phone'
    | 'job_title'
    | 'is_primary'
    | 'receives_billing'
> & {
    send_invitation?: boolean;
};

export type UpdateContactForm = Partial<
    Pick<
        BaseAccountContact,
        | 'first_name'
        | 'last_name'
        | 'email'
        | 'phone'
        | 'job_title'
        | 'is_primary'
        | 'receives_billing'
    >
>;

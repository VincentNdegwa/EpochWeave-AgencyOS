export interface ProposalNumberingSettings {
    format: string;
    prefix: string;
    delimiter: string;
    sequence_padding: number;
    next_sequence_number: number;
}

export interface ProposalSettings {
    default_validity_days: number;
    auto_archive: boolean;
    default_deposit_percentage: number;
    payment_due_days: number;
    default_terms: string | null;
    sender_name: string;
    sender_title: string | null;
    numbering: ProposalNumberingSettings;
}

export interface NotificationSettings {
    id: number;
    workspace_id: number;
    submodule: string;
    settings: {
        notifications: {
            proposals: {
                viewed: boolean;
                revisited: boolean;
                signed: boolean;
                declined: boolean;
            };
        };
    };
    created_at: string;
    updated_at: string;
}

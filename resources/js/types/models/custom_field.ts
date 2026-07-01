export interface CustomFieldDefinition {
    id: number;
    custom_field_group_id: number;
    label: string;
    field_type: string;
    options: string[] | null;
    validation_rules: string[] | null;
    is_required: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface CustomFieldGroup {
    id: number;
    workspace_id: number;
    name: string;
    applies_to: string;
    sort_order: number;
    definitions: CustomFieldDefinition[];
    created_at: string;
    updated_at: string;
}

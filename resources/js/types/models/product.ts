export type BillingType = 'one_time' | 'recurring';
export type BillingFrequency = 'none' | 'daily' | 'weekly' | 'monthly' | 'yearly';

export interface BaseProductUnit {
    id: number;
    workspace_id: number;
    name: string;
    abbreviation: string;
    created_at: string;
    updated_at: string;
}

export interface ProductUnit extends BaseProductUnit {
    workspace?: {
        id: number;
        name: string;
        display_name: string;
    } | null;
    products?: Product[] | null;
}

export interface BaseProduct {
    id: number;
    workspace_id: number;
    unit_id: number;
    name: string;
    description: string | null;
    sku: string | null;
    unit_price: number;
    billing_type: BillingType;
    billing_frequency: BillingFrequency;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Product extends BaseProduct {
    workspace?: {
        id: number;
        name: string;
        display_name: string;
    } | null;
    unit?: ProductUnit | null;
}

export type CreateProductUnitForm = Pick<
    BaseProductUnit,
    'name' | 'abbreviation'
>;

export type UpdateProductUnitForm = Partial<
    Pick<BaseProductUnit, 'name' | 'abbreviation'>
>;

export type CreateProductForm = Pick<
    BaseProduct,
    | 'unit_id'
    | 'name'
    | 'description'
    | 'sku'
    | 'unit_price'
    | 'billing_type'
    | 'billing_frequency'
    | 'is_active'
>;

export type UpdateProductForm = Partial<
    Pick<
        BaseProduct,
        | 'unit_id'
        | 'name'
        | 'description'
        | 'sku'
        | 'unit_price'
        | 'billing_type'
        | 'billing_frequency'
        | 'is_active'
    >
>;
